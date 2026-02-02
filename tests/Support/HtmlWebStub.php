<?php

declare(strict_types=1);

namespace BlueFission\SimpleClients\Tests\Support;

class HtmlWebStub
{
    private array $responses;

    public function __construct(array $responses = [])
    {
        $this->responses = $responses;
    }

    public function setResponse(string $url, DomStub $dom): void
    {
        $this->responses[$url] = $dom;
    }

    public function load(string $url): DomStub
    {
        return $this->responses[$url] ?? new DomStub([]);
    }
}

class DomStub
{
    protected array $selectors;

    public function __construct(array $selectors)
    {
        $this->selectors = $selectors;
    }

    public function find(string $selector, ?int $index = null)
    {
        $result = $this->selectors[$selector] ?? [];

        if ($index === null) {
            return $result;
        }

        if (is_array($result)) {
            return $result[$index] ?? null;
        }

        return $index === 0 ? $result : null;
    }
}

class ElementStub extends DomStub
{
    public string $plaintext = '';
    public string $href = '';
    public string $style = '';

    public function __construct(array $selectors = [], string $plaintext = '', string $href = '', string $style = '')
    {
        parent::__construct($selectors);
        $this->plaintext = $plaintext;
        $this->href = $href;
        $this->style = $style;
    }
}
