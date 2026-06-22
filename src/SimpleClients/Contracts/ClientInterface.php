<?php

namespace BlueFission\SimpleClients\Contracts;

interface ClientInterface
{
    public function configure(ClientConfig $config): static;

    public function capabilities(): ClientCapabilities;

    public function send(ClientRequest $request): ClientResponse;
}
