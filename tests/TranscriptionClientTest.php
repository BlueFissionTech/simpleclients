<?php

declare(strict_types=1);

namespace BlueFission\SimpleClients\Tests;

use BlueFission\SimpleClients\Speech\TranscriptionClient;
use BlueFission\SimpleClients\Tests\Support\HttpClientStub;
use PHPUnit\Framework\TestCase;

class TranscriptionClientTest extends TestCase
{
    public function testGcpTranscriptionNormalizes(): void
    {
        $http = new HttpClientStub();
        $http->response['body'] = json_encode([
            'results' => [
                [
                    'alternatives' => [
                        ['transcript' => 'Hello world', 'confidence' => 0.9],
                    ],
                ],
            ],
        ]);

        $client = new TranscriptionClient(['provider' => 'gcp', 'api_key' => 'gcp-key'], $http);
        $result = $client->transcribe('audio-bytes');

        $this->assertSame('Hello world', $result['text']);
        $this->assertStringContainsString('key=gcp-key', $http->calls[0]['url']);
    }

    public function testAzureTranscriptionRejectsUrl(): void
    {
        $client = new TranscriptionClient(['provider' => 'azure'], new HttpClientStub());
        $result = $client->transcribe('https://example.com/audio.wav');

        $this->assertArrayHasKey('error', $result);
    }

    public function testAwsTranscriptionSignsRequest(): void
    {
        $http = new HttpClientStub();
        $http->response['body'] = json_encode([
            'TranscriptionJob' => [
                'Transcript' => ['TranscriptFileUri' => 's3://bucket/transcript.json'],
            ],
        ]);

        $client = new TranscriptionClient([
            'provider' => 'aws',
            'access_key' => 'AKIAEXAMPLE',
            'secret_key' => 'secret',
            'media_uri' => 'https://example.com/audio.wav',
        ], $http);

        $result = $client->transcribe('ignored');

        $this->assertSame('s3://bucket/transcript.json', $result['text']);
        $this->assertSame('POST', $http->calls[0]['method']);
        $headers = strtolower(implode(',', $http->calls[0]['headers']));
        $this->assertStringContainsString('authorization:', $headers);
    }
}
