<?php
namespace App\Services;

use App\Exceptions\OperationException;
use App\Models\Country;
use Illuminate\Support\Facades\Config;
use Twilio\Rest\Client;

class TwilioService implements SmsSendingInterface
{
    private $account_sid;
    private $auth_token;

    public function __construct()
    {
        $this->account_sid = config('services.twilio.account_sid');
        $this->auth_token = config('services.twilio.token');
    }

    private function getClient()
    {
        return new Client($this->account_sid, $this->auth_token);
    }

    public function send($body, $phone)
    {
        $phone_number = $this->getClient()->lookups->v1->phoneNumbers($phone)
            ->fetch();

        if(Country::isAlphanumericSenderEnable($phone_number->countryCode)) {
            $this->getClient()->messages->create($phone,
                [
                    "from" => config('services.twilio.sender_id'),
                    'body' => $body
                ]
            );
        } else {
            $this->getClient()->messages->create($phone,
                [
                    "messagingServiceSid" => config('services.twilio.service_sid'),
                    'body' => $body
                ]
            );
        }

    }

}
