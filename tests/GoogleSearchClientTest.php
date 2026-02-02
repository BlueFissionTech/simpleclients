<?php

declare(strict_types=1);

namespace BlueFission\SimpleClients\Tests;

use BlueFission\SimpleClients\GoogleSearchClient;
use BlueFission\SimpleClients\Tests\Support\HttpFixtures;
use PHPUnit\Framework\TestCase;

class GoogleSearchClientTest extends TestCase
{
    public function testSearchParsesResults(): void
    {
        $client = new GoogleSearchClient('api-key', 'engine-id');
        $query = 'blue fission';
        $params = [
            'key' => 'api-key',
            'cx' => 'engine-id',
            'q' => $query,
        ];
        $url = 'https://www.googleapis.com/customsearch/v1?' . http_build_query($params);

        HttpFixtures::set($url, json_encode([
            'items' => [
                ['title' => 'Result 1', 'snippet' => 'Snippet 1', 'link' => 'https://example.com/1'],
            ],
        ]));

        $results = $client->search($query);

        $this->assertCount(1, $results);
        $this->assertSame('Result 1', $results[0]['title']);
    }
}
