<?php
// LocationService.php
namespace BlueFission\SimpleClients;

use BlueFission\Services\Service;

class GeoLocationClient extends Service
{
    private $baseUrl = 'http://ip-api.com/json';

    public function __construct()
    {
        parent::__construct();
    }

    public function getIpLocation()
    {
        $ip = $_SERVER['REMOTE_ADDR'];
        $url = $this->baseUrl."/{$ip}";
        $response = HttpJson::get($url);

        if (isset($response['city'])) {
            $cityState = $response['city'];
            if (isset($response['regionName'])) {
                $cityState .= ', ' . $response['regionName'];
            }

            return $cityState;
        } else {
            return 'New York';
        }
    }
}
