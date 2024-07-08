<?php

// WikipediaService.php
namespace BlueFission\SimpleClients;

use BlueFission\Services\Service;

class WikipediaClient extends Service
{
    private $baseUrl = 'https://en.wikipedia.org/w/api.php';

    public function getSummary(string $topic): string
    {
        $params = [
            'action' => 'query',
            'format' => 'json',
            'prop' => 'extracts',
            'exintro' => 'true',
            'explaintext' => 'true',
            'titles' => $topic,
        ];

        $url = $this->baseUrl . '?' . http_build_query($params);

        $response = json_decode(file_get_contents($url), true);
        $pages = $response['query']['pages'];

        foreach ($pages as $page) {
            if (isset($page['extract'])) {
                return $page['extract'];
            }
        }

        return 'No summary found for the given topic.';
    }
}
