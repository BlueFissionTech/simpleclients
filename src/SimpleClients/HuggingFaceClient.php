<?php
// HuggingFaceService.php
namespace BlueFission\SimpleClients;

use BlueFission\Services\Service;

class HuggingFaceClient extends Service
{
    private $baseUrl = 'https://huggingface.co/api';
    private $apiKey = ''; // Replace with your Hugging Face API key

    public function __construct()
    {
        $this->apiKey = env('HUGGING_FACE_API_KEY'); // Replace with your Hugging Face API key
        parent::__construct();
    }

    private function sendRequest(string $endpoint, string $method = 'GET', array $data = []): array
    {
        $headers = [
            'Authorization: Bearer ' . $this->apiKey,
            'Content-Type: application/json',
        ];

        $url = $this->baseUrl . $endpoint;

        $options = [
            'http' => [
                'method' => $method,
                'header' => implode("\r\n", $headers),
            ],
        ];

        if (!empty($data) && $method !== 'GET') {
            $options['http']['content'] = json_encode($data);
        }

        $context = stream_context_create($options);
        $response = file_get_contents($url, false, $context);
        return json_decode($response, true);
    }

    public function hasApiKey(): bool
    {
        return !empty($this->apiKey);
    }

    public function listModels(string $search = '', int $page = 1): array
    {
        $endpoint = '/models?full=true&page=' . $page;
        if (!empty($search)) {
            $endpoint .= '&search=' . urlencode($search);
        }
        return $this->sendRequest($endpoint);
    }

    public function findDatasets(string $search = '', int $page = 1): array
    {
        $endpoint = '/datasets?full=true&page=' . $page;
        if (!empty($search)) {
            $endpoint .= '&search=' . urlencode($search);
        }
        return $this->sendRequest($endpoint);
    }


    public function getModels(int $page = 1): array
    {
        return $this->sendRequest('/models?full=true&page=' . $page);
    }

    public function getDatasets(int $page = 1): array
    {
        return $this->sendRequest('/datasets?full=true&page=' . $page);
    }

    public function getModelDetails(string $modelId): array
    {
        return $this->sendRequest('/models/' . urlencode($modelId));
    }

    public function getDatasetDetails(string $datasetId): array
    {
        return $this->sendRequest('/datasets/' . urlencode($datasetId));
    }

    public function getModelUsage(string $modelId): array
    {
        return $this->sendRequest('/models/' . urlencode($modelId) . '/usage');
    }

    public function downloadDataset(string $datasetId, string $targetDir): void
    {
        $datasetDetails = $this->getDatasetDetails($datasetId);
        $filesUrl = $datasetDetails['files'];

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
        return $this->sendRequest('/models/' . urlencode($modelId) . '/usage', 'POST', $data);
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
        $endpoint = '/spaces?full=true&page=' . $page;
        if (!empty($search)) {
            $endpoint .= '&search=' . urlencode($search);
        }
        return $this->sendRequest($endpoint);
    }

    public function findSpacesByModel(string $modelId, int $page = 1): array
    {
        $endpoint = '/models/' . urlencode($modelId) . '/spaces?full=true&page=' . $page;
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

        if (!empty($queryParams)) {
            $endpoint .= '?' . http_build_query($queryParams);
        }

        $url = $spaceUrl . $endpoint;

        $options = [
            'http' => [
                'method' => $method,
                'header' => implode("\r\n", $headers),
            ],
        ];

        if (!empty($data) && $method !== 'GET') {
            $options['http']['content'] = json_encode($data);
        }

        $context = stream_context_create($options);
        $response = file_get_contents($url, false, $context);
        return json_decode($response, true);
    }
}
