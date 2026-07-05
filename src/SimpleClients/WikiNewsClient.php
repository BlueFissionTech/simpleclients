<?php

// WikiNewsRequest.php
namespace BlueFission\SimpleClients;

use BlueFission\Arr;
use BlueFission\Net\HTTP;
use BlueFission\Services\Service;
use BlueFission\Str;


class WikiNewsClient extends Service
{
    private $baseUrl = 'https://en.wikinews.org/w/api.php';

    public function getHeadlines($topic = '', $location = '')
    {
        $searchQuery = $topic;
        if (Str::isNotEmpty($location)) {
            $searchQuery .= (Str::isNotEmpty($searchQuery) ? ' AND ' : '') . $location;
        }

        $params = [
            'action' => 'query',
            'format' => 'json',
            'list' => 'search',
            'srsearch' => $searchQuery,
            'srprop' => 'size|wordcount|timestamp|snippet',
            'srlimit' => 25,
        ];

        $url = $this->baseUrl . '?' . HTTP::query($params);
        $response = HttpJson::get($url);

        $headlines = [];
        $results = Arr::make($response)->getPath('query.search', []);
        if (Arr::isNotEmpty($results)) {
            foreach ($results as $result) {
                $headlines[] = $result;
            }
        }

        return $headlines;
    }
}

