<?php

declare(strict_types=1);

namespace BlueFission\SimpleClients\Tests;

use BlueFission\SimpleClients\TrelloClient;
use BlueFission\SimpleClients\Tests\Support\CurlStub;
use PHPUnit\Framework\TestCase;

class TrelloClientTest extends TestCase
{
    public function testListBoardsUsesCurl(): void
    {
        $curl = new CurlStub();
        $curl->result = json_encode([['id' => 'board-1']]);

        $client = new TrelloClient('key', 'token', $curl);
        $boards = $client->listBoards();

        $this->assertSame('board-1', $boards[0]['id']);
        $this->assertSame('GET', $curl->config['method']);
    }
}
