<?php
// A Wrapper for OpenAI API
namespace BlueFission\SimpleClients;

use BlueFission\Services\Service;
use BlueFission\Automata\LLM\Connectors\OpenAI;

class OpenAIClient extends Service
{
    protected $_openAI;

    /**
     * OpenAIService constructor.
     */
    public function __construct()
    {
        $this->_openAI = new OpenAI(env('OPEN_AI_API_KEY'));
    }

    public function generate($input, $config = [], callable $callback = null)
    {
        $this->_openAI->generate($input, $callback, $config);
    }

    /**
     * Get GPT-3 completion based on the input.
     *
     * @param string $input
     * @return array
     */
    public function complete($input, $config = [])
    {
        return $this->_openAI->complete($input, $config);
    }

    /**
     * Get GPT-3.5 chat completion based on the input.
     *
     * @param string $input
     * @return array
     */
    public function chat($input, $config = [])
    {
        return $this->_openAI->chat($input, $config);
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
        return $this->_openAI->image($prompt, $width, $height);
    }

    /**
     * Get embeddings from the Ada model based on the input.
     *
     * @param string $input
     * @return array
     */
    public function embeddings($input)
    {
        return $this->_openAI->embeddings($input);
    }

}
