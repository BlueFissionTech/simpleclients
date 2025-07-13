<?php
// A Wrapper for OpenAI API
namespace BlueFission\SimpleClients;

use BlueFission\Automata\LLM\Connectors\OpenAI;
use BlueFission\Services\Client;

class OpenAIClient extends Client
{
    /**
     * OpenAIService constructor.
     */
    public function __construct($apiKey)
    {
        $this->_apiKey = $apiKey;
        $this->_client = new OpenAI($this->_apiKey);
    }

    public function generate($input, $config = [], callable $callback = null)
    {
        $this->_client->generate($input, $callback, $config);
    }

    /**
     * Get GPT-3 completion based on the input.
     *
     * @param string $input
     * @return array
     */
    public function complete($input, $config = [])
    {
        return $this->_client->complete($input, $config);
    }

    /**
     * Get GPT-3.5 chat completion based on the input.
     *
     * @param string $input
     * @return array
     */
    public function chat($input, $config = [])
    {
        return $this->_client->chat($input, $config);
    }

    /**
     * Get image based on the input.
     *
     * @param string $prompt
     * @param string $width
     * @param string $height
     * @return array
     */
    private function image($prompt, $width = '256', $height = '256')
    {
        return $this->_client->image($prompt, $width, $height);
    }

    /**
     * Get embeddings from the Ada model based on the input.
     *
     * @param string $input
     * @return array
     */
    public function embeddings($input)
    {
        return $this->_client->embeddings($input);
    }

}
