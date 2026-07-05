<?php
// HuggingFaceService.php
namespace BlueFission\SimpleClients;

use BlueFission\Arr;
use BlueFission\Net\HTTP;
use BlueFission\Services\Service;
use BlueFission\Str;
use BlueFission\Val;

class HuggingFaceClient extends Service
{
    private $baseUrl = 'https://huggingface.co/api';
    private $apiKey = '';

    public function __construct(?string $apiKey = null, ?string $baseUrl = null)
    {
        if ($baseUrl) {
            $this->baseUrl = Str::trim($baseUrl, '/');
        }
        $this->apiKey = $apiKey ?? $this->getEnv('HUGGING_FACE_API_KEY');
        parent::__construct();
    }

    private function sendRequest(string $endpoint, string $method = 'GET', array $data = []): array
    {
        $headers = [
            'Authorization: Bearer ' . $this->apiKey,
            'Content-Type: application/json',
        ];

        $url = $this->baseUrl . $endpoint;

        return HttpJson::request($method, $url, $headers, $data);
    }

    public function hasApiKey(): bool
    {
        return Val::isNotEmpty($this->apiKey);
    }

    public function listModels(string $search = '', int $page = 1): array
    {
        $endpoint = $this->apiEndpoint('/models', ['full' => 'true', 'page' => $page], $search);
        return $this->sendRequest($endpoint);
    }

    public function findDatasets(string $search = '', int $page = 1): array
    {
        $endpoint = $this->apiEndpoint('/datasets', ['full' => 'true', 'page' => $page], $search);
        return $this->sendRequest($endpoint);
    }


    public function getModels(int $page = 1): array
    {
        return $this->sendRequest($this->apiEndpoint('/models', ['full' => 'true', 'page' => $page]));
    }

    public function getDatasets(int $page = 1): array
    {
        return $this->sendRequest($this->apiEndpoint('/datasets', ['full' => 'true', 'page' => $page]));
    }

    public function getModelDetails(string $modelId): array
    {
        return $this->sendRequest('/models/' . $this->urlSegment($modelId));
    }

    public function getDatasetDetails(string $datasetId): array
    {
        return $this->sendRequest('/datasets/' . $this->urlSegment($datasetId));
    }

    public function getModelUsage(string $modelId): array
    {
        return $this->sendRequest('/models/' . $this->urlSegment($modelId) . '/usage');
    }

    public function downloadDataset(string $datasetId, string $targetDir): void
    {
        $datasetDetails = $this->getDatasetDetails($datasetId);
        $filesUrl = Arr::make($datasetDetails)->getPath('files', '');

        $zipFile = "{$targetDir}/{$datasetId}.zip";

        file_put_contents($zipFile, fopen($filesUrl, 'r'));

        $zip = new ZipArchive;
        if ($zip->open($zipFile) === TRUE) {
            $zip->extractTo($targetDir);
            $zip->close();
        }
    }

    public function useModel(string $modelId, string $inputText): array
    {
        $data = [
            'inputs' => $inputText,
        ];
        return $this->sendRequest('/models/' . $this->urlSegment($modelId) . '/usage', 'POST', $data);
    }

    public function createHostedInstance(string $repoUrl, string $token): array
    {
        $data = [
            'url' => $repoUrl,
            'token' => $token,
        ];
        return $this->sendRequest('/repos/create', 'POST', $data);
    }

    public function findSpaces(string $search = '', int $page = 1): array
    {
        $endpoint = $this->apiEndpoint('/spaces', ['full' => 'true', 'page' => $page], $search);
        return $this->sendRequest($endpoint);
    }

    public function findSpacesByModel(string $modelId, int $page = 1): array
    {
        $endpoint = $this->apiEndpoint('/models/' . $this->urlSegment($modelId) . '/spaces', ['full' => 'true', 'page' => $page]);
        return $this->sendRequest($endpoint);
    }

    public function getSpaceDetails(string $spaceId): array
    {
        return $this->sendRequest('/spaces/' . $spaceId);
    }

    public function useSpace(string $spaceName, string $endpoint, string $method = 'GET', array $data = [], array $queryParams = []): array
    {
        $spaceUrl = "https://{$spaceName}.spaces.huggingface.co";
        $headers = [
            'Authorization: Bearer ' . $this->apiKey,
            'Content-Type: application/json',
        ];

        if (Arr::isNotEmpty($queryParams)) {
            $endpoint .= '?' . HTTP::query($queryParams);
        }

        $url = $spaceUrl . $endpoint;

        return HttpJson::request($method, $url, $headers, $data);
    }

    private function getEnv(string $key): string
    {
        if (function_exists('env')) {
            return (string)env($key);
        }

        $value = getenv($key);
        return $value === false ? '' : (string)$value;
    }

    private function apiEndpoint(string $path, array $query, string $search = ''): string
    {
        if (Val::isNotEmpty($search)) {
            $query['search'] = $search;
        }

        return $path . '?' . HTTP::query($query);
    }

    private function urlSegment(string $value): string
    {
        return HTTP::pathSegment($value);
    }
}
