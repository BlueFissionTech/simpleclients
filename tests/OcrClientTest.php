<?php

declare(strict_types=1);

namespace BlueFission\SimpleClients\Tests;

use BlueFission\SimpleClients\Vision\OcrClient;
use BlueFission\SimpleClients\Tests\Support\HttpClientStub;
use PHPUnit\Framework\TestCase;

class OcrClientTest extends TestCase
{
    public function testGcpOcrBuildsRequestAndNormalizes(): void
    {
        $http = new HttpClientStub();
        $http->response['body'] = json_encode([
            'responses' => [
                [
                    'textAnnotations' => [
                        ['description' => 'Hello OCR'],
                    ],
                ],
            ],
        ]);

        $client = new OcrClient(['provider' => 'gcp', 'api_key' => 'gcp-key'], $http);
        $result = $client->analyze('image-bytes');

        $this->assertSame('Hello OCR', $result['text']);
        $this->assertCount(1, $http->calls);
        $this->assertStringContainsString('key=gcp-key', $http->calls[0]['url']);
        $this->assertSame('POST', $http->calls[0]['method']);
        $this->assertArrayHasKey('requests', $http->calls[0]['body']);
    }

    public function testAzureOcrUsesUrlPayload(): void
    {
        $http = new HttpClientStub();
        $http->response['body'] = json_encode([
            'regions' => [
                [
                    'lines' => [
                        ['words' => [['text' => 'Azure'], ['text' => 'OCR']]],
                    ],
                ],
            ],
        ]);

        $client = new OcrClient(['provider' => 'azure', 'endpoint' => 'https://azure.example.com', 'token' => 'tok'], $http);
        $result = $client->analyze('https://example.com/image.png');

        $this->assertSame("Azure OCR", $result['text']);
        $this->assertSame('POST', $http->calls[0]['method']);
        $this->assertSame('https://azure.example.com/vision/v3.2/ocr', $http->calls[0]['url']);
        $this->assertSame(['url' => 'https://example.com/image.png'], $http->calls[0]['body']);
    }

    public function testAwsOcrRequiresCredentials(): void
    {
        $http = new HttpClientStub();
        $client = new OcrClient(['provider' => 'aws'], $http);
        $result = $client->analyze('bytes');

        $this->assertArrayHasKey('error', $result);
        $this->assertSame('AWS credentials required for Textract OCR.', $result['error']);
        $this->assertCount(0, $http->calls);
    }
}
