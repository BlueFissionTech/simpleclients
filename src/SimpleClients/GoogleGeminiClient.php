<?php

namespace BlueFission\SimpleClients;

use RuntimeException;
use BlueFission\Services\Client;
use BlueFission\SimpleClients\Concerns\ProviderClientHelpers;
use BlueFission\Arr;
use BlueFission\Val;

class GoogleGeminiClient extends Client {
	use ProviderClientHelpers;

	public function __construct( $apiKey, $client = null ) {
		$this->_apiKey = $apiKey;
		$this->_client = $client ?? $this->resolveClient();
	}

    private function resolveClient() {
        if (Runtime::classAvailable('\\Gemini')) {
    		return \Gemini::client($this->_apiKey);
    	}

    	throw new RuntimeException('Gemini client not available; provide a client instance to the constructor.');
    }

    /**
     * Generate text from Gemini.
     */
    public function generate($input, $config = []): string
    {
    	$input = $this->normalizeInput($input);
        $result = $this->_client->geminiPro()->generateContent($input, $config);
        return $this->extractText($result);
    }

    /**
     * Complete text from Gemini.
     */
    public function complete($input, $config = []): string
	{
		$input = $this->normalizeInput($input);
		$result = $this->_client->geminiPro()->generateContent($input, $config);
		return $this->extractText($result);
	}

    /**
     * Chat/Respond with Gemini.
     */
	public function chat($input, $config = [], $history = [])
	{
		$input = $this->normalizeInput($input);
		$chat = $this->_client->geminiPro();

		if (Arr::size($history) > 0 && Runtime::objectCanCall($chat, 'startChat')) {
			$chat->startChat(history: $history);
		}

		return $this->extractText($chat->sendMessage($input, $config));
	}

	public function respond($input, $config = [], $history = [])
	{
		return $this->chat($input, $config, $history);
	}

	public function embeddings($input): array
	{
		$input = $this->normalizeInput($input);
		if (!Runtime::objectCanCall($this->_client, 'embeddingModel')) {
			return [];
		}

		$response = $this->_client
		 ->embeddingModel()
		 ->embedContent($input);

		if (Arr::is($response)) {
			return $response;
		}

		$values = Arr::make($response)->getPath('embedding.values');
		if (Val::isNotNull($values)) {
			return Arr::make($values)->toArray();
		}

		return [];
	}

	private function normalizeInput($input) {
		if (Runtime::objectCanCall($input, 'prompt')) {
			return $this->providerPrompt($input);
		}

		return $input;
	}

	private function extractText($response): string
	{
		if (Runtime::objectCanCall($response, 'text')) {
			return (string)$response->text();
		}

		if (Arr::is($response)) {
			return (string)($response['text'] ?? '');
		}

		return (string)$response;
	}
}
