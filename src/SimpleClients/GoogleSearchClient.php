<?php

// GoogleSearchService.php
namespace BlueFission\SimpleClients;

use BlueFission\Arr;
use BlueFission\Net\HTTP;
use BlueFission\Services\Service;
use BlueFission\Val;

class GoogleSearchClient extends Service
{
    private $baseUrl = 'https://www.googleapis.com/customsearch/v1';
    private $apiKey = '';
    private $searchEngineId = '';

    public function  __construct(?string $apiKey = null, ?string $searchEngineId = null)
    {
        $this->apiKey = $apiKey ?? $this->getEnv('GOOGLE_SEARCH_API_ID');
        $this->searchEngineId = $searchEngineId ?? $this->getEnv('GOOGLE_SEARCH_ENGINE_ID');
        parent::__construct();
    }

    public function hasApiKey(): bool
    {
        return Val::isNotEmpty($this->apiKey);
    }

    public function search(string $query): array
    {
        $params = [
            'key' => $this->apiKey,
            'cx' => $this->searchEngineId,
            'q' => $query,
        ];

        $url = $this->baseUrl . '?' . HTTP::query($params);
        $response = HttpJson::get($url);

        $results = [];
        $items = Arr::make($response)->getPath('items', []);
        if (Arr::isNotEmpty($items)) {
            foreach ($items as $item) {
                $results[] = [
                    'title' => $item['title'],
                    'snippet' => $item['snippet'],
                    'link' => $item['link'],
                ];
            }
        }

        return $results;
    }

    private function getEnv(string $key): string
    {
        return Runtime::env($key);
    }
}
