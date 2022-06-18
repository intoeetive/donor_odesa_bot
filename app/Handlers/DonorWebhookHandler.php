<?php

namespace App\Handlers;

use DefStudio\Telegraph\Handlers\WebhookHandler;
use DefStudio\Telegraph\Keyboard\Button;
use DefStudio\Telegraph\Keyboard\Keyboard;
use DefStudio\Telegraph\Keyboard\ReplyKeyboard;
use DefStudio\Telegraph\Models\TelegraphBot;
use DefStudio\Telegraph\Enums\ChatActions;
use DefStudio\Telegraph\Telegraph;
use Revolution\Google\Sheets\Facades\Sheets;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Stringable;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Exception;

use App\Models\Donor;
use App\Models\BloodType;
use App\Models\BloodRequest;
use App\Models\DonorBloodRequestResponse;
use App\Models\DonorTelegramChat;

class DonorWebhookHandler extends WebhookHandler
{
    public function start(): void
    {
        if (!empty($this->message) && empty($this->chat->name)) {
            try {
                $this->chat->name = $this->message->from()->firstName() . ' ' . $this->message->from()->lastName();
                $this->chat->save();
            } catch (Exception $e) {
                $this->reply("Помилка збереження.");
            }
        }

        //if there a donor already for this chat?
        if(! empty($this->chat->donor)) {
            if (config('telegraph.debug_mode')) {
                Log::debug('Donor exists');
            }
            if (!empty($this->chat->donor->phone)) {
                //already registered!
                if (config('telegraph.debug_mode')) {
                    Log::debug('Donor has phone number');
                }
                $this->welcomeBack($this->chat->donor);
                return;
            } else {
                $this->chat
                    ->markdown(__('messages.message.welcome'))
                    ->send();
                $this->requestMissingDonorData('phone');
                return;
            }
        } else {
            if (config('telegraph.debug_mode')) {
                Log::debug('Donor does not exist');
            }
            $this->chat
                ->markdown(__('messages.message.welcome'))
                ->send();
            $this->requestMissingDonorData('phone');
            return;
        }
    }

    /**
     * Show what we have on current donor
     *
     * @return void
     */
    public function whoami(): void
    {
        $data = 'Ім\'я: ' . $this->chat->donor->name . "\n";
        $data .= 'Телефон: ' . $this->chat->donor->phone . "\n";
        $data .= 'Група крові: ' . (!empty($this->chat->donor->blood_type_id) ? BloodType::BLOOD_TYPES[$this->chat->donor->blood_type_id] : '') . "\n";
        $data .= 'Рік народження: ' . $this->chat->donor->birth_year . "\n";
        $data .= 'Вага: ' . ($this->chat->donor->weight_ok === 1 ? 'Більше 55 кг' : ($this->chat->donor->weight_ok === 0 ? 'Менше 55 кг' : ''))  . "\n";
        $data .= 'Наявність протипоказань: ' . ($this->chat->donor->no_contras === 1 ? 'Немає' : ($this->chat->donor->no_contras === 0 ? 'Є' : '')) . "\n";
        $data .= 'Дата останнього донорства: ' . $this->chat->donor->last_donorship_date;
        $this->chat
                ->markdown($data)
                ->send();
    }

    /**
     * Let the donor remove themselves from database
     *
     * @return void
     */
    public function delete(): void
    {
        $this->chat->donor->delete();
        unset($this->chat->donor);
        $this->chat
                ->markdown('Данні видалено')
                ->send();
        $this->start();
    }

    /**
     * Process data sent as messages (not button clicks)
     *
     * @param Stringable $text
     * @return void
     */
    protected function handleChatMessage(Stringable $text): void
    {
        //is this contact?
        if (! empty($this->message->contact())) {
            $this->share_phone($this->message->contact()->phone_number());
            return;
        }

        if(! empty($this->chat->donor) && ! $text->isEmpty()) {

            if (config('telegraph.debug_mode')) {
                Log::debug('What are they sending?', [$this->chat->donor->name, $this->chat->donor->birth_year, $text, is_numeric($text)]);
            }

            //are they sharing name?
            if (empty($this->chat->donor->name) && !is_numeric($text->value())) {
                $this->share_name($text->title());
                return;
            }

            //are they sharing birth year?
            if (empty($this->chat->donor->birth_year) && is_numeric($text->value())) {
                $this->share_birth_year($text->value());
                return;
            }

            //are they sharing last donorship date?
            if (empty($this->chat->donor->last_donorship_date)) {
                $last_donorship_date = Carbon::parse($text->value())->locale('uk')->toDateTimeString();
                $this->share_last_donorship_date($last_donorship_date);
                return;
            }
        }
    }

    /**
     * Donor returning back
     * welcome and collect missing data
     *
     * @param Donor $donor
     * @return void
     */
    private function welcomeBack(Donor $donor)
    {
        if (config('telegraph.debug_mode')) {
            Log::debug('welcomeBack');
        }
        //do we have all data?
        $missingData = $this->checkMissingDonorData($donor);
        if (config('telegraph.debug_mode')) {
            Log::debug('Missing data:', [$missingData]);
        }
        if (!empty($missingData)) {
            $this->chat
                ->markdown(__('messages.message.welcome_back_data_missing'))
                ->send();
            $this->requestMissingDonorData($missingData);
            return;
        } else {
            //show 'welcome back' message
            $this->chat
                ->markdown(__('messages.message.welcome_back'))
                ->send();
        }
    }

    /**
     * Return the next missing piece of data that we need to ask
     * Keeping each check individual because we might need different order
     *
     * @param Donor $donor
     * @return string property
     */
    private function checkMissingDonorData(Donor $donor)
    {
        if (empty($donor->phone)) {
            return 'phone';
        }
        if (empty($donor->name)) {
            return 'name';
        }
        if ($donor->blood_type_id === null) {
            return 'blood_type_id';
        }
        if ($donor->birth_year === null) {
            return 'birth_year';
        }
        if ($donor->weight_ok === null) {
            return 'weight_ok';
        }
        if ($donor->no_contras === null) {
            return 'no_contras';
        }
        if ($donor->last_donorship_date === null) {
            return 'last_donorship_date_yes_no';
        }
        return false;
    }

    /**
     * Request donor data for given key
     *
     * @param string $property
     * @return void
     */
    private function requestMissingDonorData($property)
    {
        $this->cleanKeyboard();

        if (empty($property)) {
            $this->chat
                ->markdown(__('messages.request.thank_you'))
                ->send();
            return;
        }

        if ($property == 'no_contras') {
            $message = $this->chat->photo(Storage::path('bot_files/contras.jpg'))->markdown(__('messages.request.' . $property));
        } else {
            $message = $this->chat->markdown(__('messages.request.' . $property));
        }
        //$message->reply($this->messageId);
        $keyboard = $this->buildMessageKeyboard($property);
        if (!empty($keyboard)) {
            if (config('telegraph.debug_mode')) {
                Log::debug('Keyboard: ', $keyboard->toArray());
            }
            if ($keyboard instanceof ReplyKeyboard) {
                $message->replyKeyboard($keyboard)->send();
            } else {
                $message->keyboard($keyboard)->send();
            }
        } else {
            $message->send();
        }
        if (config('telegraph.debug_mode')) {
            Log::debug('Requested: ', $message->toArray());
        }
    }

    /**
     * Build the buttons for each data request
     *
     * @param string $property
     * @return Keyboard
     */
    private function buildMessageKeyboard($property)
    {
        switch ($property) {
            case 'blood_type_id':
                $buttons = [];
                foreach (BloodType::BLOOD_TYPES as $id => $name)
                {
                    $buttons[] = Button::make($name)->action('share_' . $property)->param($property, $id);
                }
                $keyboard = Keyboard::make()->buttons($buttons)->chunk(2);
                break;
            case 'phone':
                $keyboard = ReplyKeyboard::make()->button(__('messages.button.share_' . $property))->requestContact();//->oneTime();
                break;
            case 'weight_ok':
                $keyboard = Keyboard::make()->buttons([
                    Button::make(__('messages.button.more_55_kg'))->action('share_' . $property)->param('weight_ok', '1'),
                    Button::make(__('messages.button.less_55_kg'))->action('share_' . $property)->param('weight_ok', '0'),
                ]);
                break;
            case 'no_contras':
                $keyboard = Keyboard::make()->buttons([
                    Button::make(__('messages.button.have_no_contraindications'))->action('share_' . $property)->param('no_contras', '1'),
                    Button::make(__('messages.button.have_contraindications'))->action('share_' . $property)->param('no_contras', '0'),
                ]);
                break;
            case 'last_donorship_date_yes_no':
                $keyboard = Keyboard::make()->buttons([
                    Button::make(__('messages.button.last_donorship_yes'))->action('share_' . $property)->param('last_donorship', '1'),
                    Button::make(__('messages.button.last_donorship_no'))->action('share_' . $property)->param('last_donorship', '0'),
                ]);
                break;
            default:
                $keyboard = null;
                break;
        }
        return $keyboard;
    }

    /**
     * Show 'donor denied' message
     * @return bool
     */
    private function denyDonor($reason = ''): bool
    {
        $this->chat
            ->markdown(__('messages.request.not_acceptable' . (!empty($reason) ? '.' . $reason : '')))
            ->send();
        return false;
    }

    private function cleanKeyboard()
    {
        if (! config('telegraph.debug_mode')) {
            $this->chat->deleteKeyboard($this->messageId)->send();
        }
    }

    public function share_phone($phone): void
    {
        if (strpos($phone, '+') !== 0) {
            $phone = '+' . $phone;
        }
        $this->chat->markdown('*' . $phone . '*')->removeReplyKeyboard()->send();

        //take the phone number and look up in the database
        if(! empty($this->chat->donor)) {
            $donor = $this->chat->donor;
            $donor->phone = $phone;
            $donor->save();
        } else {
            $donor = Donor::where('phone', $phone)->first();
        }
        if(! empty($donor)) {
            //associate donor with this chat
            try {
                $this->cleanKeyboard();
                $this->chat->donor()->associate($donor);
                $this->chat->save();
                $this->welcomeBack($donor);
            } catch (Exception $e) {
                $this->reply("Помилка збереження.");
            }
            return;
        } else {
            try {
                $this->cleanKeyboard();
                $donor = $this->chat->donor()->create([
                    'phone' => $phone
                ]);
                if (config('telegraph.debug_mode')) {
                    Log::debug('Donor created: ', $donor->toArray());
                }
                $this->chat->donor()->associate($donor);
                $this->chat->save();

                $missingData = $this->checkMissingDonorData($donor);
                $this->requestMissingDonorData($missingData);
            } catch (Exception $e) {
                $this->reply("Помилка збереження.");
            }
        }
    }

    public function share_name($data): void
    {
        $this->chat->markdown('*' . $data . '*')->send();

        try {
            $this->cleanKeyboard();
            $this->chat->donor->name = $data;
            $this->chat->donor->save();

            $missingData = $this->checkMissingDonorData($this->chat->donor);
            $this->requestMissingDonorData($missingData);
        } catch (Exception $e) {
            $this->reply("Помилка збереження.");
        }
    }

    public function share_blood_type_id(): void
    {
        $data = $this->data->get('blood_type_id');
        $this->chat->markdown('*' . BloodType::BLOOD_TYPES[$data] . '*')->send();

        try {
            $this->cleanKeyboard();
            $this->chat->donor->blood_type_id = $data;
            $this->chat->donor->save();

            $missingData = $this->checkMissingDonorData($this->chat->donor);
            $this->requestMissingDonorData($missingData);
        } catch (Exception $e) {
            $this->reply("Помилка збереження.");
        }
    }

    public function share_birth_year($data): void
    {
        if (config('telegraph.debug_mode')) {
            Log::debug('birth year', [$data, ! is_numeric($data), $data != (int) $data, strlen($data) != 2, strlen($data) != 4]);
        }

        if (! is_numeric($data) || $data != (int) $data || (strlen($data) != 2 && strlen($data) != 4)) {
            $this->chat
                ->markdown(__('messages.request.need_only_birth_year'))
                ->send();
            return;
        }

        //if just to digits provided, guess century
        if (strlen($data) == 2) {
            if ($data > Carbon::now()->format('y')) {
                $data = '19' . $data;
            } else {
                $data = '20' . $data;
            }
        }

        $this->chat->markdown('*' . $data . '*')->send();

        $maxYear = Carbon::now()->year - 18;
        $minYear = Carbon::now()->year - 64;
        if ($data > $maxYear || $data < $minYear) {
            $this->denyDonor('birth_year');
            return;
        }

        try {
            $this->chat->donor->birth_year = $data;
            $this->chat->donor->save();
            if (config('telegraph.debug_mode')) {
                Log::debug('birth_year:', [ $this->chat->donor->birth_year]);
            }
    
            $missingData = $this->checkMissingDonorData($this->chat->donor);
            $this->requestMissingDonorData($missingData);
        } catch (Exception $e) {
            $this->reply("Помилка збереження.");
        }
    }

    public function share_weight_ok(): void
    {
        $data = $this->data->get('weight_ok');
        $options = [
            '0' => __('messages.button.less_55_kg'),
            '1' => __('messages.button.more_55_kg'),
        ];
        $this->chat->markdown('*' . $options[$data] . '*')->send();

        if ($data < 1) {
            $this->cleanKeyboard();
            $this->denyDonor('weight_ok');
            return;
        }

        try {
            $this->cleanKeyboard();
            $this->chat->donor->weight_ok = 1;
            $this->chat->donor->save();
            $missingData = $this->checkMissingDonorData($this->chat->donor);
            $this->requestMissingDonorData($missingData);
        } catch (Exception $e) {
            $this->reply("Помилка збереження.");
        }
    }

    public function share_no_contras(): void
    {
        $data = $this->data->get('no_contras');
        $options = [
            '0' => __('messages.button.have_contraindications'),
            '1' => __('messages.button.have_no_contraindications'),
        ];
        $this->chat->markdown('*' . $options[$data] . '*')->send();

        if ($data < 1) {
            $this->cleanKeyboard();
            $this->denyDonor('no_contras');
            return;
        }

        try {
            $this->cleanKeyboard();
            $this->chat->donor->no_contras = $data;
            $this->chat->donor->save();
            $missingData = $this->checkMissingDonorData($this->chat->donor);
            $this->requestMissingDonorData($missingData);
        } catch (Exception $e) {
            $this->reply("Помилка збереження.");
        }
    }

    public function share_last_donorship_date_yes_no(): void
    {
        $data = $this->data->get('last_donorship');
        $options = [
            '0' => __('messages.button.last_donorship_no'),
            '1' => __('messages.button.last_donorship_yes'),
        ];
        $this->chat->markdown('*' . $options[$data] . '*')->send();

        $this->cleanKeyboard();

        if ($data < 1) {
            $this->chat
                ->markdown(__('messages.request.thank_you'))
                ->send();
        } else {
            $this->chat
                ->markdown(__('messages.request.last_donorship_date'))
                ->send();
        }
    }

    public function share_last_donorship_date($data): void
    {
        try {
            $this->cleanKeyboard();
            $this->chat->donor->last_donorship_date = $data;
            $this->chat->donor->save();

            $this->chat
                ->markdown(__('messages.request.thank_you'))
                ->send();
        } catch (Exception $e) {
            $this->reply("Помилка збереження.");
        }
    }

    /**
     * When user received donorship invitation
     *
     * @return void
     */
    public function respondDonorRequest()
    {
        if ($this->data->get('opt_in') < 1) {
            $this->chat
                ->markdown(__('messages.response.see_you_next_time'))
                ->send();
            return;
        }

        $blood_request_id = (int) $this->data->get('blood_request_id');

        $bloodRequest = BloodRequest::where('id', $blood_request_id)->withCount('responses')->first();
        if ($bloodRequest->responses_count >= $bloodRequest->qty) {
            $this->chat
                ->markdown(__('messages.response.blood_request_already_closed'))
                ->send();
            return;
        }

        $this->chat
            ->photo(Storage::path('bot_files/contras.jpg'))
            ->markdown(__('messages.response.thank_you'))
            ->keyboard(Keyboard::make()->buttons([
                Button::make(__('messages.button.yes_i_will_do_it'))->action('recordDonorResponseYes')->param('blood_request_id', (string) $blood_request_id),
                Button::make(__('messages.button.no_i_can_not'))->action('recordDonorResponseNo')->param('blood_request_id', (string) $blood_request_id),
            ]))
            ->send();
    }

    /**
     * Record donor response into donor_blood_request_responses 
     *
     * @return void
     */

     public function recordDonorResponseYes()
     {
         $no_response_contras = 1;
         $this->recordDonorResponse($no_response_contras);
     }

    public function recordDonorResponseNo()
    {
        $no_response_contras = 0;
        $this->recordDonorResponse($no_response_contras);
    }
    
    private function recordDonorResponse($data)
    {
        $this->cleanKeyboard();

        if ($data < 1) {
            $this->chat
                ->markdown(__('messages.response.see_you_next_time'))
                ->send();
            return;
        }

        $bloodRequest = BloodRequest::where('id', $this->data->get('blood_request_id'))->withCount('responses')->first();

        if ($bloodRequest->responses_count >= $bloodRequest->qty) {
            $this->chat
                ->markdown(__('messages.response.blood_request_already_closed'))
                ->send();
            return;
        }

        $response = DonorBloodRequestResponse::create([
            'no_response_contras' => $data,
            'confirmation_date' => Carbon::now()->toDateTimeString()
        ]);
        $response->blood_request_id = $bloodRequest->id;
        $response->location_id = $bloodRequest->location_id;
        $response->donor()->associate($this->chat->donor);
        $response->save();

        $this->chat
                ->markdown($bloodRequest->location->bot_instructions)
                ->send();
    }

    public function confirmDonorship()
    {
        $this->cleanKeyboard();

        $data = $this->data->get('confirm');
        $response = DonorBloodRequestResponse::where('id', $this->data->get('blood_request_response_id'))->first();

        if ($data < 1) {
            $response->no_donorship = 1;
            $response->save();
            $this->chat
                ->markdown(__('messages.response.could_not_donor_we_are_sorry'))
                ->send();
            return;
        }

        
        $response->donorship_date =  Carbon::now()->subDays(1)->toDateTimeString();
        $response->save();

        $response->donor->last_donorship_date = $response->donorship_date;
        $response->donor->save();

        $this->chat
                ->markdown(__('messages.response.thank_you_for_donorship'))
                ->send();
    }
}
