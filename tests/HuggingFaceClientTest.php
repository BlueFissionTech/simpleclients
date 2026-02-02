<?php

declare(strict_types=1);

namespace BlueFission\SimpleClients\Tests;

use BlueFission\SimpleClients\HuggingFaceClient;
use BlueFission\SimpleClients\Tests\Support\HttpFixtures;
use PHPUnit\Framework\TestCase;

class HuggingFaceClientTest extends TestCase
{
    public function testListModelsReturnsResponse(): void
    {
        $client = new HuggingFaceClient('hf-key');
        $url = 'https://huggingface.co/api/models?full=true&page=1&search=bert';

        HttpFixtures::set($url, json_encode([
            ['modelId' => 'bert-base-uncased'],
        ]));

        $results = $client->listModels('bert');

        $this->assertSame('bert-base-uncased', $results[0]['modelId']);
    }
}
