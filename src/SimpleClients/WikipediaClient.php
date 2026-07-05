<?php

// WikipediaService.php
namespace BlueFission\SimpleClients;

use BlueFission\Arr;
use BlueFission\Net\HTTP;
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

        $url = $this->baseUrl . '?' . HTTP::query($params);

        $response = HttpJson::get($url);
        $pages = Arr::make($response)->getPath('query.pages', []);

        foreach ($pages as $page) {
            if (Arr::hasKey($page, 'extract')) {
                return $page['extract'];
            }
        }

        return 'No summary found for the given topic.';
    }
}
