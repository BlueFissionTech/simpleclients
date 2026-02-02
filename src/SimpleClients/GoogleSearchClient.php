<?php

// GoogleSearchService.php
namespace BlueFission\SimpleClients;

use BlueFission\Services\Service;

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
        return !empty($this->apiKey);
    }

    public function search(string $query): array
    {
        $params = [
            'key' => $this->apiKey,
            'cx' => $this->searchEngineId,
            'q' => $query,
        ];

        $url = $this->baseUrl . '?' . http_build_query($params);
        $response = json_decode(file_get_contents($url), true);

        $results = [];
        if (isset($response['items'])) {
            foreach ($response['items'] as $item) {
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
        if (function_exists('env')) {
            return (string)env($key);
        }

        $value = getenv($key);
        return $value === false ? '' : (string)$value;
    }
}
