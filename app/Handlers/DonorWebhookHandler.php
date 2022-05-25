<?php

namespace App\Handlers;

use DefStudio\Telegraph\Handlers\WebhookHandler;
use DefStudio\Telegraph\Keyboard\Button;
use DefStudio\Telegraph\Keyboard\Keyboard;

class DonorWebhookHandler extends WebhookHandler
{
    public function start()
    {
        // maybe we have a record already?
        //return $this->confirmationExistingUser();

        $this->chat
            ->markdown(__('messages.message.welcome'))
            ->keyboard(Keyboard::make()->buttons([
                Button::make(__('messages.button.sharePhoneNumber'))->action('sharePhoneNumber')->param('id', '42'),
            ]))
            ->send();
    }

    public function sharePhoneNumber() {
        //take the phone number and look up in the database

        //if it's there - skip to confirmation
        //return $this->confirmationExistingUser();

        //if it's new user, walk through the registration process

        //ask for blood type
        $this->chat
            ->markdown(__('messages.message.your_blood_type'))
            ->keyboard(Keyboard::make()->buttons([
                Button::make('I+ (O+)')->action('shareBloodType')->param('type', '1+'),
                Button::make('II+ (A+)')->action('shareBloodType')->param('type', '2+'),
                Button::make('III+ (B+)')->action('shareBloodType')->param('type', '3+'),
                Button::make('IV+ (AB+)')->action('shareBloodType')->param('type', '4+'),
                Button::make('I- (O-)')->action('shareBloodType')->param('type', '1-'),
                Button::make('II- (A-)')->action('shareBloodType')->param('type', '2-'),
                Button::make('III- (B-)')->action('shareBloodType')->param('type', '3-'),
                Button::make('IV- (AB-)')->action('shareBloodType')->param('type', '4-'),
            ]))
            ->send();
    }

    public function shareBloodType()
    {
        //record the blood type

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
        //record the name

        //sync the data to Google Sheet
        $this->sendDataToGoogleSheet();

        //show them confirmation message
        $this->chat
            ->markdown(__('messages.message.thank_you'))
            ->send();
    }

    private function sendDataToGoogleSheet()
    {

    }

    private function confirmationExistingUser()
    {

    }
}
