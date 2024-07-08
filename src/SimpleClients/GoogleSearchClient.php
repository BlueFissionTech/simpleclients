<?php

// GoogleSearchService.php
namespace BlueFission\SimpleClients;

use BlueFission\Services\Service;

class GoogleSearchClient extends Service
{
    private $baseUrl = 'https://www.googleapis.com/customsearch/v1';
    private $apiKey = ''; // Replace with your Google API key
    private $searchEngineId = ''; // Replace with your search engine ID

    public function  __construct()
    {
        $this->apiKey = env('GOOGLE_SEARCH_API_ID'); // Replace with your Google API key
        $this->searchEngineId = env('GOOGLE_SEARCH_ENGINE_ID'); // Replace with your search engine ID
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
}
