<?php

namespace BlueFission\SimpleClients;

use BlueFission\Services\Service;
use BlueFission\Net\HTTP;
use BlueFission\Connections\Curl;

class MurfAIClient extends Service
{
    private $apiKey;
    protected $_curl;

    public function __construct($apiKey)
    {
        $this->apiKey = $apiKey;
        $this->_curl = new Curl([
            'method' => 'post',
        ]);

        $headers = [
            'Content-Type: application/x-www-form-urlencoded',
            'Authorization: Bearer ' . $this->apiKey,
        ];

        $this->_curl->config('headers', $headers);
    }

    public function generateAudio($text, $voice, $format)
    {
        // Use the murf.ai API to generate an AI vocal representation of the text
        $request_data = [
            'text' => $text,
            'voice' => $voice,
            'format' => $format,
        ];

        $this->_curl->config('target', 'https://api.murf.ai/tts');
        $this->_curl->open();
        $this->_curl->query(http_build_query($request_data));
        $response = $this->_curl->getResult();
        $this->_curl->close();

        $responseBody = json_decode($response, true);
        return $responseBody['data']['link'];
    }
}
