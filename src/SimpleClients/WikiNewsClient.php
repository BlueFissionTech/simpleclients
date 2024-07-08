<?php

// WikiNewsRequest.php
namespace BlueFission\SimpleClients;

use BlueFission\Services\Service;


class WikiNewsClient extends Service
{
    private $baseUrl = 'https://en.wikinews.org/w/api.php';

    public function getHeadlines($topic = '', $location = '')
    {
        $searchQuery = $topic;
        if ($location) {
            $searchQuery .= ($searchQuery ? ' AND ' : '') . $location;
        }

        $params = [
            'action' => 'query',
            'format' => 'json',
            'list' => 'search',
            'srsearch' => $searchQuery,
            'srprop' => 'size|wordcount|timestamp|snippet',
            'srlimit' => 25,
        ];

        $url = $this->baseUrl . '?' . http_build_query($params);
        $response = json_decode(file_get_contents($url), true);

        $headlines = [];
        if (isset($response['query']['search'])) {
            foreach ($response['query']['search'] as $result) {
                $headlines[] = $result;
            }
        }

        return $headlines;
    }
}

