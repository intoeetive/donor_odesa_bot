<?php

namespace App\Handlers;

use DefStudio\Telegraph\Handlers\WebhookHandler;
use DefStudio\Telegraph\Keyboard\Button;
use DefStudio\Telegraph\Keyboard\Keyboard;
use DefStudio\Telegraph\Enums\ChatActions;
use DefStudio\Telegraph\Telegraph;
use Revolution\Google\Sheets\Facades\Sheets;
use Exception;

class DonorWebhookHandler extends WebhookHandler
{
    public function start(): void
    {
        //start with saving this chat
        $this->bot->chats()->firstOrCreate([
            'chat_id' => $this->chat->chat_id,
            'name' => $this->message->from()->firstName() . ' ' . $this->message->from()->lastName(),
        ]);
        
        // maybe we have a record already?
        //return $this->confirmationExistingUser();

        $this->chat
            ->markdown(__('messages.message.welcome'))
            ->keyboard(Keyboard::make()->buttons([
                Button::make(__('messages.button.sharePhoneNumber'))->action('sharePhoneNumber')->param('id', '42'),
            ]))
            ->send();
    }

    public function sharePhoneNumber(): void
    {
        //first, do some cleanup
        $this->chat->deleteKeyboard($this->messageId)->send();
        $this->chat->markdown('*+380123456578*')->send();

        //take the phone number and look up in the database

        //if it's there - skip to confirmation
        //return $this->confirmationExistingUser();

        //if it's new user, walk through the registration process
        try {
            $this->chat->phone = '+380123456578';
            $this->chat->save();
        } catch (Exception $e) {
            $this->reply("Помилка збереження.")->send();
        }

        //ask for blood type
        $this->chat
            ->markdown(__('messages.message.your_blood_type'))
            ->keyboard(Keyboard::make()->buttons([
                Button::make('I+ (O+)')->action('shareBloodType')->param('type', '1')->param('rh', '+'),
                Button::make('II+ (A+)')->action('shareBloodType')->param('type', '2')->param('rh', '+'),
                Button::make('III+ (B+)')->action('shareBloodType')->param('type', '3')->param('rh', '+'),
                Button::make('IV+ (AB+)')->action('shareBloodType')->param('type', '4')->param('rh', '+'),
                Button::make('I- (O-)')->action('shareBloodType')->param('type', '1')->param('rh', '-'),
                Button::make('II- (A-)')->action('shareBloodType')->param('type', '2')->param('rh', '-'),
                Button::make('III- (B-)')->action('shareBloodType')->param('type', '3')->param('rh', '-'),
                Button::make('IV- (AB-)')->action('shareBloodType')->param('type', '4')->param('rh', '-'),
            ])->chunk(2))
            ->send();
    }

    public function shareBloodType(): void
    {
        $this->chat->deleteKeyboard($this->messageId)->send();
        //record the blood type

        switch ($this->data->get('type')) {
            case '1':
                $type = 'I (1)';
                break;
            case '2':
                $type = 'II (2)';
                break;
            case '3':
                $type = 'III (3)';
                break;
            case '4':
            default:
                $type = 'IV (4)';
                break;
        }
        $rh = $this->data->get('rh', '+');

        try {
            $this->chat->blood_type = $type;
            $this->chat->blood_rh = $rh;
            $this->chat->save();
        } catch (Exception $e) {
            $this->reply("Помилка збереження.")->send();
        }

        $this->chat->markdown("*{$type}{$rh}*")->send();

        //now ask for name
        $this->chat
            ->markdown(__('messages.message.your_name'))
            ->keyboard(Keyboard::make()->buttons([
                Button::make(__('messages.button.shareName'))->action('shareName')->param('id', '42'),
            ]))
            ->send();
    }

    public function shareName()
    {
        $this->chat->deleteKeyboard($this->messageId)->send();
        //record the name

        $this->chat->markdown("*{$this->chat->name}*")->send();
        //sync the data to Google Sheet
        $this->sendDataToGoogleSheet();

        //show them confirmation message
        $this->chat
            ->markdown(__('messages.message.thank_you'))
            ->send();
    }

    private function sendDataToGoogleSheet()
    {
        $append = [
            "Отметка времени" => "",
            "Прізвище, Ім'я" => $this->chat->name,
            "Група крові" => $this->chat->blood_type,
            "Резус-фактор" => $this->chat->blood_rh,
            "Ваш мобільный телефон" => $this->chat->phone,
        ];
        try {
            Sheets::spreadsheet(config('google.spreadsheet_id'))
              ->sheetById(config('google.sheet_id'))
              ->append([$append]);
        } catch (\Google\Service\Exception $e) {
            $this->chat->reply("Помилка збереження данних")->send();
        }
        return true;
    }

    private function confirmationExistingUser()
    {

    }
}
