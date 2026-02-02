<?php

declare(strict_types=1);

namespace BlueFission\SimpleClients\Tests;

use BlueFission\SimpleClients\Video\AnalysisClient;
use BlueFission\SimpleClients\Tests\Support\HttpClientStub;
use PHPUnit\Framework\TestCase;

class VideoAnalysisClientTest extends TestCase
{
    public function testGcpVideoAnalysis(): void
    {
        $http = new HttpClientStub();
        $http->response['body'] = json_encode([
            'annotationResults' => [
                [
                    'segmentLabelAnnotations' => [
                        ['entity' => ['description' => 'Cat']],
                    ],
                ],
            ],
        ]);

        $client = new AnalysisClient(['provider' => 'gcp'], $http);
        $result = $client->analyze('video-bytes');

        $this->assertCount(1, $result['labels']);
        $this->assertSame('POST', $http->calls[0]['method']);
    }

    public function testAzureVideoAnalysisUsesUrl(): void
    {
        $http = new HttpClientStub();
        $http->response['body'] = json_encode(['videos' => ['ok']]);

        $client = new AnalysisClient([
            'provider' => 'azure',
            'endpoint' => 'https://api.videoindexer.ai',
            'account_id' => 'account-1',
            'location' => 'trial',
        ], $http);
        $result = $client->analyze('https://example.com/video.mp4');

        $this->assertSame(['ok'], $result['labels']);
        $this->assertSame('https://api.videoindexer.ai/trial/Accounts/account-1/Videos', $http->calls[0]['url']);
    }

    public function testAwsVideoAnalysisRequiresCredentials(): void
    {
        $http = new HttpClientStub();
        $client = new AnalysisClient(['provider' => 'aws'], $http);
        $result = $client->analyze('video-bytes');

        $this->assertArrayHasKey('error', $result);
        $this->assertSame('AWS Rekognition video analysis requires S3 bucket/key.', $result['error']);
        $this->assertCount(0, $http->calls);
    }
}
