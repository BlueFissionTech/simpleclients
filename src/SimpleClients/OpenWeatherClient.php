<?php
// WeatherRequest.php
namespace BlueFission\SimpleClients;

use BlueFission\Services\Service;

class OpenWeatherClient extends Service
{
    private $apiKey;
    private $baseUrl = 'https://api.openweathermap.org/data/2.5/weather';

    public function __construct(?string $apiKey = null)
    {
        $this->apiKey = $apiKey ?? $this->getEnv('OPEN_WEATHER_API_KEY');
        parent::__construct();
    }

    public function getWeatherByLocation($location)
    {
        $params = [
            'q' => $location,
            'appid' => $this->apiKey,
            'units' => 'imperial', // Use 'metric' for Celsius and 'imperial' for Fahrenheit
        ];

        $url = $this->baseUrl . '?' . http_build_query($params);
        $response = json_decode(file_get_contents($url), true);

        if (isset($response['main'], $response['weather'][0])) {
            $temperature = $response['main']['temp'];
            $description = $response['weather'][0]['description'];

            return "The current temperature in {$location} is {$temperature}°F with {$description}.";
        } else {
            return "Unable to fetch weather data for {$location}.";
        }
    }

    private function getEnv(string $key): string
    {
        if (function_exists('env')) {
            return (string)env($key);
        }

        $value = getenv($key);
        return $value === false ? '' : (string)$value;
    }
}
