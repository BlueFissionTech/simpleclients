<?php

declare(strict_types=1);

namespace BlueFission\SimpleClients\Tests;

use BlueFission\SimpleClients\DuckDuckGoSearchClient;
use BlueFission\SimpleClients\Tests\Support\DomStub;
use BlueFission\SimpleClients\Tests\Support\ElementStub;
use BlueFission\SimpleClients\Tests\Support\HtmlWebStub;
use PHPUnit\Framework\TestCase;

class DuckDuckGoSearchClientTest extends TestCase
{
    public function testSearchParsesResults(): void
    {
        $title = new ElementStub([], 'Duck Result');
        $urlElement = new ElementStub([], '', 'https://duckduckgo.com/l/?uddg=https%3A%2F%2Fexample.com');
        $snippet = new ElementStub([], 'Snippet');

        $resultElement = new ElementStub([
            '.result__title a' => $title,
            '.result__url' => $urlElement,
            '.result__snippet' => $snippet,
        ]);

        $dom = new DomStub([
            '.result' => [$resultElement],
        ]);

        $htmlWeb = new HtmlWebStub([
            'https://duckduckgo.com/html?q=ai' => $dom,
        ]);

        $client = new DuckDuckGoSearchClient($htmlWeb);
        $results = $client->search('ai');

        $this->assertCount(1, $results);
        $this->assertSame('Duck Result', $results[0]['title']);
        $this->assertSame('https://example.com', $results[0]['link']);
        $this->assertSame('Snippet', $results[0]['snippet']);
    }
}
