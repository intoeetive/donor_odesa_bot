<?php

namespace App\Handlers;

use DefStudio\Telegraph\Handlers\WebhookHandler;
use DefStudio\Telegraph\Keyboard\Button;
use DefStudio\Telegraph\Keyboard\Keyboard;
use DefStudio\Telegraph\Enums\ChatActions;
use DefStudio\Telegraph\Telegraph;
use Revolution\Google\Sheets\Facades\Sheets;
use Illuminate\Support\Facades\Storage;
use Exception;

use App\Models\Donor;
use App\Models\BloodType;

class DonorWebhookHandler extends WebhookHandler
{
    public function start(): void
    {
        //start with saving this chat
        $this->bot->chats()->firstOrCreate([
            'chat_id' => $this->chat->chat_id,
        ]);

        //if there a donor already for this chat?
        $donor = Donor::where('chat_id', $this->chat->chat_id)->get();
        if(! $donor->isEmpty()) {
            if (!empty($donor->phone)) {
                //already registered!
                $this->welcomeBack($donor);
            } else {
                $this->chat
                    ->markdown(__('messages.message.welcome'))
                    ->send();
                $this->requestMissingDonorData('phone');
            }
        } else {
            $this->chat
                ->markdown(__('messages.message.welcome'))
                ->send();
        }

        if (!empty($this->message)) {
            try {
                $this->chat->name = $this->message->from()->firstName() . ' ' . $this->message->from()->lastName();
                $this->chat->save();
            } catch (Exception $e) {
                $this->reply("Помилка збереження.");
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
        //do we have all data?
        $missingData = $this->checkMissingDonorData($donor);
        if (!empty($missingData)) {
            $this->chat
                ->markdown(__('messages.message.welcome_back_data_missing'))
                ->send();
            $this->requestMissingDonorData($missingData);
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
        $this->chat->deleteKeyboard($this->messageId)->send();

        $message = $this->chat->markdown(__('messages.request.' . $property));
        $keyboard = $this->buildMessageKeyboard($property);
        if (!empty($keyboard)) {
            $message->keyboard($keyboard);
        }
        $message->send();
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
                    $buttons[] = Button::make($name)->action('store_' . $property)->param($property, $id);
                }
                $keyboard = Keyboard::make()->buttons($buttons)->chunk(2);
                break;
            case 'phone':
                $keyboard = Keyboard::make()->buttons([
                    Button::make(__('messages.button.share_' . $property))->action('share_' . $property),
                ]);
                break;
            case 'name':
                $keyboard = Keyboard::make()->buttons([
                    Button::make(__('messages.button.share_' . $property))->action('share_' . $property),
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
     */
    private function denyDonor($reason = '')
    {
        $this->chat
            ->markdown(__('messages.request.not_acceptable' . (!empty($reason) ? '.' . $reason : '')))
            ->send();
        return false;
    }

    public function share_phone(): void
    {
        //first, do some cleanup
        $this->chat->deleteKeyboard($this->messageId)->send();

        $phone = '+380123456578';
        $this->chat->markdown('*{$phone}*')->send();

        //take the phone number and look up in the database
        $donor = Donor::where('phone', $phone)->get();
        if(! $donor->isEmpty()) {
            //associate donor with this chat
            try {
                $this->chat->donor = $donor;
                $this->chat->save();
            } catch (Exception $e) {
                $this->reply("Помилка збереження.");
            }
            $this->welcomeBack($donor);
        } else {
            $donor = Donor::create([
                'phone' => $phone
            ]);
            $donor->telegramChat = $this->chat;
        }

        $missingData = $this->checkMissingDonorData($donor);
        $this->requestMissingDonorData($missingData);
    }

    public function share_name(): void
    {
        //first, do some cleanup
        $this->chat->deleteKeyboard($this->messageId)->send();

        $data = $this->message->from()->firstName() . ' ' . $this->message->from()->lastName();
        $this->chat->markdown('*{$data}*')->send();

        try {
            $this->chat->donor->name = $data;
            $this->chat->donor->save();
        } catch (Exception $e) {
            $this->reply("Помилка збереження.");
        }

        $missingData = $this->checkMissingDonorData($this->chat->donor);
        $this->requestMissingDonorData($missingData);
    }

    public function share_blood_type_id(): void
    {
        //first, do some cleanup
        $this->chat->deleteKeyboard($this->messageId)->send();

        $data = $this->data->get('blood_type_id');
        $this->chat->markdown('*{$data}*')->send();

        try {
            $this->chat->donor->blood_type_id = $data;
            $this->chat->donor->save();
        } catch (Exception $e) {
            $this->reply("Помилка збереження.");
        }

        $missingData = $this->checkMissingDonorData($this->chat->donor);
        $this->requestMissingDonorData($missingData);
    }

    public function share_birth_year(): void
    {
        //first, do some cleanup
        $this->chat->deleteKeyboard($this->messageId)->send();

        $data = $this->data->get('birth_year');
        $this->chat->markdown('*{$data}*')->send();

        $requiredYear = Carbon::now()->year - 18;
        if ($data < $requiredYear) {
            return $this->denyDonor('birth_year');
        }

        try {
            $this->chat->donor->birth_year = $data;
            $this->chat->donor->save();
        } catch (Exception $e) {
            $this->reply("Помилка збереження.");
        }

        $missingData = $this->checkMissingDonorData($this->chat->donor);
        $this->requestMissingDonorData($missingData);
    }

    public function share_weight_ok(): void
    {
        //first, do some cleanup
        $this->chat->deleteKeyboard($this->messageId)->send();

        $data = $this->data->get('weight_ok');
        $this->chat->markdown('*{$data}*')->send();

        if ($data < 55) {
            return $this->denyDonor('weight_ok');
        }

        try {
            $this->chat->donor->weight_ok = 1;
            $this->chat->donor->save();
        } catch (Exception $e) {
            $this->reply("Помилка збереження.");
        }

        $missingData = $this->checkMissingDonorData($this->chat->donor);
        $this->requestMissingDonorData($missingData);
    }

    public function share_no_contras(): void
    {
        //first, do some cleanup
        $this->chat->deleteKeyboard($this->messageId)->send();

        $data = $this->data->get('no_contras');
        $this->chat->markdown('*{$data}*')->send();

        if ($data < 1) {
            return $this->denyDonor('no_contras');
        }

        try {
            $this->chat->donor->weight_ok = 1;
            $this->chat->donor->save();
        } catch (Exception $e) {
            $this->reply("Помилка збереження.");
        }

        //last step, show them success message
        $this->chat
            ->markdown(__('messages.request.thank_you'))
            ->send();
    }

    /**
     * When user received donorship invitation
     *
     * @return void
     */
    public function respondDonorRequest()
    {
        $this->chat
            ->photo(Storage::path('public/contras.jpg'))
            ->markdown(__('messages.response.thank_you'))
            ->keyboard(Keyboard::make()->buttons([
                Button::make(__('messages.button.yes_i_will_do_it'))->action('recordDonorResponse'),
            ]))
            ->send();
    }

    /**
     * Record donor response into donor_blood_request_responses 
     *
     * @return void
     */
    public function recordDonorResponse()
    {

    }
}
