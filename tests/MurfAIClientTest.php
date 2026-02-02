<?php

declare(strict_types=1);

namespace BlueFission\SimpleClients\Tests;

use BlueFission\SimpleClients\MurfAIClient;
use BlueFission\SimpleClients\Tests\Support\CurlStub;
use PHPUnit\Framework\TestCase;

class MurfAIClientTest extends TestCase
{
    public function testGenerateAudioReturnsLink(): void
    {
        $curl = new CurlStub();
        $curl->result = json_encode(['data' => ['link' => 'https://audio.example.com/file.mp3']]);

        $client = new MurfAIClient('murf-key', $curl);
        $link = $client->generateAudio('Hello', 'voice-1', 'mp3');

        $this->assertSame('https://audio.example.com/file.mp3', $link);
        $this->assertSame('https://api.murf.ai/tts', $curl->config['target']);
    }
}
