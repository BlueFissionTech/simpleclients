<?php

// DuckDuckGoSearchService.php
namespace BlueFission\SimpleClients;

use BlueFission\Services\Service;
use simplehtmldom\HtmlWeb;

class DuckDuckGoSearchClient extends Service
{
    private $baseUrl = 'https://duckduckgo.com/html';
    private $htmlWeb;

    public function __construct($htmlWeb = null)
    {
        parent::__construct();
        $this->htmlWeb = $htmlWeb ?? new HtmlWeb();
    }

    public function search(string $query): array
    {
        $url = $this->baseUrl . '?q=' . urlencode($query);

        $html = $this->htmlWeb->load($url);

        $results = [];
        foreach ($html->find('.result') as $resultElement) {
            $titleElement = $resultElement->find('.result__title a', 0);
            $urlElement = $resultElement->find('.result__url', 0);
            $snippetElement = $resultElement->find('.result__snippet', 0);

            if ($titleElement && $urlElement && $snippetElement) {
                $parts = parse_url($urlElement->href);
                parse_str($parts['query'], $query);
                $url = $query['uddg'];
                $results[] = [
                    'title' => $titleElement->plaintext,
                    'link' => $url,
                    'snippet' => $snippetElement->plaintext
                ];
            }
        }

        return $results;
    }
}
