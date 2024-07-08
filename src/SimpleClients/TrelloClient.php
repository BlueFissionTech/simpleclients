<?php

namespace BlueFission\SimpleClients;

use BlueFission\Services\Service;
use BlueFission\Connections\Curl;

class TrelloClient extends Service
{
    private $curl;
    private $apiKey;
    private $apiToken;

    public function __construct()
    {
        parent::__construct();
        $this->apiKey = env('TRELLO_API_KEY');
        $this->apiToken = env('TRELLO_API_TOKEN');
        $this->curl = new Curl();
    }

    private function request(string $method, string $url, array $params = [])
    {
        $params = array_merge([
            'key' => $this->apiKey,
            'token' => $this->apiToken,
        ], $params);

        $this->curl->config([
            'target' => 'https://api.trello.com/1/' . $url,
            'method' => $method,
        ]);

        $this->curl->open();
        $this->curl->query($params);
        $response = $this->curl->result();
        $this->curl->close();

        return json_decode($response, true);
    }

    public function listBoards()
    {
        // Replace {memberId} with your Trello memberId or 'me' for the authorized user
        return $this->request('GET', 'members/{memberId}/boards');
    }

    public function listColumns(string $boardId)
    {
        return $this->request('GET', "boards/{$boardId}/lists");
    }

    public function listUsers(string $boardId)
    {
        return $this->request('GET', "boards/{$boardId}/members");
    }

    public function addCard(string $name, string $desc, string $columnId)
    {
        return $this->request('POST', 'cards', [
            'name' => $name,
            'desc' => $desc,
            'idList' => $columnId,
        ]);
    }

    public function removeCard(string $cardId)
    {
        return $this->request('DELETE', "cards/{$cardId}");
    }

    public function assignCard(string $cardId, string $userId)
    {
        return $this->request('POST', "cards/{$cardId}/idMembers", [
            'value' => $userId,
        ]);
    }

    public function moveCard(string $cardId, string $columnId)
    {
        return $this->request('PUT', "cards/{$cardId}", [
            'idList' => $columnId,
        ]);
    }
}
