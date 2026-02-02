<?php

declare(strict_types=1);

namespace BlueFission\SimpleClients\Tests;

use BlueFission\SimpleClients\WikiNewsClient;
use BlueFission\SimpleClients\Tests\Support\HttpFixtures;
use PHPUnit\Framework\TestCase;

class WikiNewsClientTest extends TestCase
{
    public function testGetHeadlinesReturnsSearchResults(): void
    {
        $client = new WikiNewsClient();
        $topic = 'AI';
        $params = [
            'action' => 'query',
            'format' => 'json',
            'list' => 'search',
            'srsearch' => $topic,
            'srprop' => 'size|wordcount|timestamp|snippet',
            'srlimit' => 25,
        ];
        $url = 'https://en.wikinews.org/w/api.php?' . http_build_query($params);

        HttpFixtures::set($url, json_encode([
            'query' => [
                'search' => [
                    ['title' => 'News 1'],
                    ['title' => 'News 2'],
                ],
            ],
        ]));

        $results = $client->getHeadlines($topic);

        $this->assertCount(2, $results);
        $this->assertSame('News 1', $results[0]['title']);
    }
}
