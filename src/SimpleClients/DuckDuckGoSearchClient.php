<?php

// DuckDuckGoSearchService.php
namespace BlueFission\SimpleClients;

use BlueFission\Arr;
use BlueFission\Net\HTTP;
use BlueFission\Services\Service;
use BlueFission\Val;
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
        $url = $this->baseUrl . '?' . HTTP::query(['q' => $query]);

        $html = $this->htmlWeb->load($url);

        $results = [];
        foreach ($html->find('.result') as $resultElement) {
            $titleElement = $resultElement->find('.result__title a', 0);
            $urlElement = $resultElement->find('.result__url', 0);
            $snippetElement = $resultElement->find('.result__snippet', 0);

            if (Val::isNotEmpty($titleElement) && Val::isNotEmpty($urlElement) && Val::isNotEmpty($snippetElement)) {
                $parts = HTTP::urlParts($urlElement->href) ?? [];
                parse_str(Arr::make($parts)->getPath('query', ''), $queryParams);
                $url = Arr::make($queryParams)->getPath('uddg', '');
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
