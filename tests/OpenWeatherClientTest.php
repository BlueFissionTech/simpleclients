<?php

declare(strict_types=1);

namespace BlueFission\SimpleClients\Tests;

use BlueFission\SimpleClients\OpenWeatherClient;
use BlueFission\SimpleClients\Tests\Support\HttpFixtures;
use PHPUnit\Framework\TestCase;

class OpenWeatherClientTest extends TestCase
{
    public function testGetWeatherByLocationFormatsResponse(): void
    {
        $client = new OpenWeatherClient('weather-key');
        $params = [
            'q' => 'Paris',
            'appid' => 'weather-key',
            'units' => 'imperial',
        ];
        $url = 'https://api.openweathermap.org/data/2.5/weather?' . http_build_query($params);

        HttpFixtures::set($url, json_encode([
            'main' => ['temp' => 72],
            'weather' => [['description' => 'clear sky']],
        ]));

        $result = $client->getWeatherByLocation('Paris');

        $this->assertSame('The current temperature in Paris is 72°F with clear sky.', $result);
    }
}
