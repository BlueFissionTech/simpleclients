<?php

declare(strict_types=1);

namespace BlueFission\SimpleClients\Tests;

use BlueFission\SimpleClients\Contracts\ClientCapabilities;
use BlueFission\SimpleClients\Contracts\ClientConfig;
use BlueFission\SimpleClients\Contracts\ClientInterface;
use BlueFission\SimpleClients\Contracts\ClientRequest;
use BlueFission\SimpleClients\Contracts\ClientResponse;
use PHPUnit\Framework\TestCase;

class ClientContractsTest extends TestCase
{
    public function testConfigRequestAndResponseExposeStableArrays(): void
    {
        $config = new ClientConfig([
            'auth' => ['api_key' => 'test-key'],
            'base_url' => 'https://provider.example',
            'headers' => ['Accept' => 'application/json'],
            'options' => ['timeout' => 10],
        ]);

        $request = new ClientRequest([
            'method' => 'post',
            'url' => '/records',
            'headers' => ['Content-Type' => 'application/json'],
            'query' => ['page' => 1],
            'body' => ['name' => 'Ada'],
        ]);

        $response = ClientResponse::success(['id' => 12], 201, ['request_id' => 'abc']);

        $this->assertSame('test-key', $config->auth()['api_key']);
        $this->assertSame('https://provider.example', $config->baseUrl());
        $this->assertSame('POST', $request->method());
        $this->assertSame(['page' => 1], $request->query());
        $this->assertTrue($response->ok());
        $this->assertSame(201, $response->status());
        $this->assertSame(['id' => 12], $response->data());
        $this->assertSame('abc', $response->meta()['request_id']);
    }

    public function testFailureResponseAndCapabilitiesUsePredictableShapes(): void
    {
        $failure = ClientResponse::failure('timeout', 504, ['retryable' => true]);
        $capabilities = new ClientCapabilities([
            'service' => 'records',
            'actions' => ['list', 'read'],
            'auth' => ['api_key'],
            'transports' => ['http'],
            'retry' => ['safe_methods' => ['GET']],
            'config' => ['base_url'],
        ]);

        $this->assertFalse($failure->ok());
        $this->assertSame('timeout', $failure->error());
        $this->assertSame(504, $failure->status());
        $this->assertSame('records', $capabilities->service());
        $this->assertSame(['list', 'read'], $capabilities->actions());
        $this->assertSame(['api_key'], $capabilities->auth());
    }

    public function testContractMembersConstrainUnexpectedShapes(): void
    {
        $config = new ClientConfig([
            'auth' => 'token',
            'headers' => false,
            'options' => 'fast',
        ]);

        $request = new ClientRequest([
            'method' => 'post',
            'headers' => 'application/json',
            'query' => 'page=1',
        ]);

        $this->assertSame([], $config->auth());
        $this->assertSame([], $config->headers());
        $this->assertSame([], $config->options());
        $this->assertSame('POST', $request->method());
        $this->assertSame([], $request->headers());
        $this->assertSame([], $request->query());
    }

    public function testInterfaceSupportsAReusableClientBoundary(): void
    {
        $client = new class implements ClientInterface {
            private ClientConfig $config;

            public function configure(ClientConfig $config): static
            {
                $this->config = $config;

                return $this;
            }

            public function capabilities(): ClientCapabilities
            {
                return new ClientCapabilities([
                    'service' => 'notifications',
                    'actions' => ['send'],
                    'auth' => ['token'],
                ]);
            }

            public function send(ClientRequest $request): ClientResponse
            {
                return ClientResponse::success([
                    'base_url' => $this->config->baseUrl(),
                    'method' => $request->method(),
                    'url' => $request->url(),
                ]);
            }
        };

        $client->configure(new ClientConfig(['base_url' => 'https://notify.example']));
        $response = $client->send(new ClientRequest(['method' => 'post', 'url' => '/messages']));

        $this->assertSame('notifications', $client->capabilities()->service());
        $this->assertSame([
            'base_url' => 'https://notify.example',
            'method' => 'POST',
            'url' => '/messages',
        ], $response->data());
    }
}
