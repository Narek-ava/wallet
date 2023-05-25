<?php


namespace App\Services;


interface SmsSendingInterface
{
    public function send($body, $phone);

}
