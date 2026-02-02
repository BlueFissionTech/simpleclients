<?php

declare(strict_types=1);

namespace BlueFission\SimpleClients\Tests;

use BlueFission\SimpleClients\GeoLocationClient;
use BlueFission\SimpleClients\Tests\Support\HttpFixtures;
use PHPUnit\Framework\TestCase;

class GeoLocationClientTest extends TestCase
{
    public function testGetIpLocationReturnsCityState(): void
    {
        $_SERVER['REMOTE_ADDR'] = '1.2.3.4';
        HttpFixtures::set('http://ip-api.com/json/1.2.3.4', json_encode([
            'city' => 'Austin',
            'regionName' => 'Texas',
        ]));

        $client = new GeoLocationClient();
        $location = $client->getIpLocation();

        $this->assertSame('Austin, Texas', $location);
    }
}
