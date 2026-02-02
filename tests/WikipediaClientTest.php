<?php

declare(strict_types=1);

namespace BlueFission\SimpleClients\Tests;

use BlueFission\SimpleClients\WikipediaClient;
use BlueFission\SimpleClients\Tests\Support\HttpFixtures;
use PHPUnit\Framework\TestCase;

class WikipediaClientTest extends TestCase
{
    public function testGetSummaryReturnsExtract(): void
    {
        $client = new WikipediaClient();
        $topic = 'OpenAI';
        $params = [
            'action' => 'query',
            'format' => 'json',
            'prop' => 'extracts',
            'exintro' => 'true',
            'explaintext' => 'true',
            'titles' => $topic,
        ];
        $url = 'https://en.wikipedia.org/w/api.php?' . http_build_query($params);

        HttpFixtures::set($url, json_encode([
            'query' => [
                'pages' => [
                    '123' => ['extract' => 'Summary text'],
                ],
            ],
        ]));

        $summary = $client->getSummary($topic);

        $this->assertSame('Summary text', $summary);
    }
}
