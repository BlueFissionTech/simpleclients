<?php

namespace BlueFission\SimpleClients;

use BlueFission\Arr;
use BlueFission\Net\HTTP;
use BlueFission\SimpleClients\Contracts\ClientCapabilities;
use BlueFission\SimpleClients\Contracts\ClientConfig;
use BlueFission\SimpleClients\Contracts\ClientInterface;
use BlueFission\SimpleClients\Contracts\ClientRequest;
use BlueFission\SimpleClients\Contracts\ClientResponse;
use BlueFission\Str;
use BlueFission\Val;

class HttpJsonContractClient implements ClientInterface
{
    private ClientConfig $config;

    public function __construct(?ClientConfig $config = null)
    {
        $this->config = $config ?? new ClientConfig();
    }

    public function configure(ClientConfig $config): static
    {
        $this->config = $config;

        return $this;
    }

    public function capabilities(): ClientCapabilities
    {
        return new ClientCapabilities([
            'service' => 'http_json',
            'actions' => ['send'],
            'auth' => ['headers'],
            'transports' => ['http'],
            'config' => ['base_url', 'headers', 'options'],
        ]);
    }

    public function send(ClientRequest $request): ClientResponse
    {
        $url = $this->url($request);
        $headers = Arr::make($this->config->headers())->merge($request->headers())->toArray();
        $data = HttpJson::request($request->method(), $url, $headers, $request->body(), []);

        return ClientResponse::success($data, 200, [
            'method' => $request->method(),
            'url' => $url,
        ]);
    }

    private function url(ClientRequest $request): string
    {
        $baseUrl = Str::trim($this->config->baseUrl(), '/');
        $path = Str::trim($request->url(), '/');
        $url = Val::isNotEmpty($baseUrl) ? $baseUrl . '/' . $path : $request->url();

        if (Arr::isNotEmpty($request->query())) {
            $url .= (Str::has($url, '?') ? '&' : '?') . HTTP::query($request->query());
        }

        return $url;
    }
}
