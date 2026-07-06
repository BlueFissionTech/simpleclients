<?php

declare(strict_types=1);

namespace BlueFission\SimpleClients\Tests;

use BlueFission\SimpleClients\Contracts\ClientCapabilities;
use BlueFission\SimpleClients\Contracts\ClientConfig;
use BlueFission\SimpleClients\Contracts\ClientInterface;
use BlueFission\SimpleClients\Contracts\ClientRequest;
use BlueFission\SimpleClients\Contracts\ClientResponse;
use BlueFission\SimpleClients\Contracts\OptionalDependency;
use BlueFission\SimpleClients\Contracts\ProviderConfig;
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

    public function testClientResponseNormalizesProviderResults(): void
    {
        $success = ClientResponse::fromProviderResult([
            'message' => 'ok',
            'status' => 202,
        ], 200, ['provider' => 'grok']);

        $failure = ClientResponse::fromProviderResult([
            'error' => 'rate limited',
            'status' => 429,
        ]);

        $http = ClientResponse::fromHttpJson(['accepted' => true], 201, ['X-Trace' => 'abc']);

        $this->assertTrue($success->ok());
        $this->assertSame(202, $success->status());
        $this->assertSame('grok', $success->meta()['provider']);
        $this->assertFalse($failure->ok());
        $this->assertSame('rate limited', $failure->error());
        $this->assertSame(429, $failure->status());
        $this->assertTrue($http->ok());
        $this->assertSame(['accepted' => true], $http->data());
        $this->assertSame(['accepted' => true], $http->body());
        $this->assertSame(['X-Trace' => 'abc'], $http->headers());
        $this->assertSame(201, $http->toNetResponse()->getStatusCode());
        $this->assertSame(['X-Trace' => 'abc'], $http->toNetResponse()->getHeaders());
        $this->assertSame(['accepted' => true], $http->toServiceResponse()->data);
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

    public function testProviderConfigUsesContractObjectConstraints(): void
    {
        $config = new ProviderConfig([
            'provider' => 'azure',
            'endpoint' => 'https://provider.example',
            'auth' => ['api_key' => 'key'],
            'headers' => 'bad-headers',
            'query' => ['language' => 'en-US'],
            'options' => false,
        ]);

        $this->assertSame('azure', $config->provider());
        $this->assertSame('https://provider.example', $config->endpoint());
        $this->assertSame(['api_key' => 'key'], $config->auth());
        $this->assertSame([], $config->headers());
        $this->assertSame(['language' => 'en-US'], $config->query());
        $this->assertSame([], $config->options());
        $this->assertSame([
            'provider' => 'azure',
            'endpoint' => 'https://provider.example',
            'auth' => ['api_key' => 'key'],
            'headers' => [],
            'query' => ['language' => 'en-US'],
            'options' => [],
        ], $config->toArray());

        $config
            ->config('endpoint', 'https://next.example')
            ->config([
                'headers' => ['Accept' => 'application/json'],
                'options' => 'invalid-options',
            ]);

        $this->assertSame('https://next.example', $config->config('endpoint'));
        $this->assertSame(['Accept' => 'application/json'], $config->headers());
        $this->assertSame([], $config->options());
        $this->assertSame('azure', $config->config()['provider']);
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

    public function testOptionalDependencyReportsAvailabilityAndInstallHint(): void
    {
        $available = OptionalDependency::forClass(
            'bluefission/develation',
            ClientResponse::class,
            'contract responses'
        );

        $missing = OptionalDependency::forClass(
            'vendor/missing-sdk',
            'Vendor\\Missing\\Client',
            'missing provider',
            'composer require vendor/missing-sdk'
        );

        $this->assertTrue($available->available());
        $this->assertSame('', $available->message());
        $this->assertFalse($missing->available());
        $this->assertSame(
            'missing provider: requires optional package: vendor/missing-sdk: composer require vendor/missing-sdk',
            $missing->message()
        );
        $this->assertSame('missing provider', $missing->toArray()['capability']);
        $this->assertFalse($missing->toArray()['available']);
    }
}
