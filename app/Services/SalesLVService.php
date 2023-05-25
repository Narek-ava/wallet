<?php


namespace App\Services;

class SalesLVService implements SmsSendingInterface
{
    const API_ADDRESS = 'https://traffic.sales.lv/API:0.16/';

    private string $api_key;
    private string $account_sid;

    public function __construct()
    {
        $this->api_key = config('services.saleslv.api_key');
        $this->account_sid = config('services.saleslv.service_sid');
    }

    private function getClient()
    {
        return new \GuzzleHttp\Client();
    }

    protected function getHeaders()
    {
        return ['headers' => ['Content-Type' => 'application/json', 'Accept' => 'application/x-www-form-urlencoded']];
    }

    public function send($body, $phone)
    {
        $data = $this->getHeaders() ?? [];
        $client = $this->getClient();

        $data['json'] = [
            'APIKey' => $this->api_key,
            'Sender' => $this->account_sid,
            'Command' => 'Send',
            'Recipients' => $phone,
            'Content' => $body
        ];

        $responseJSON = $client->request('POST', self::API_ADDRESS, $data)->getBody()->getContents();
        $response = json_decode($responseJSON, true);

        foreach ($response as $eachSendMessageInfo) {
            if ($eachSendMessageInfo['Invalid'] !== false) {
                logger()->error('SalesLvErrorResponse', $response);
                throw new \Exception(t('ui_unable_to_send_code'));
            }
        }


        return $response;
    }


}
