<?php

namespace App\Tests;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\HttpClient;
use Webmozart\Assert\Assert;

class GameTest extends TestCase
{
    private const BASE_URI = 'http://caddy:80';

    private function postMove(int $gameId, int $player, int $horizontal, int $vertical): ?int
    {
        $client = HttpClient::createForBaseUri(self::BASE_URI);

        $move = [
            'player' => $player,
            'horizontal' => $horizontal,
            'vertical' => $vertical,
        ];

        $response = $client->request('POST', '/games/' . $gameId . '/move', [
            'body' => \Safe\json_encode($move)
        ]);

        $responseData = $response->toArray();

        $this->assertArrayHasKey('winner', $responseData);
        Assert::nullOrInteger($responseData['winner']);

        return $responseData['winner'];
    }

    private function startGame(): int
    {
        $client = HttpClient::createForBaseUri(self::BASE_URI);
        $response = $client->request('POST', '/games');
        $responseContent = $response->toArray();

        $this->assertSame(201, $response->getStatusCode());
        $this->assertArrayHasKey('id', $responseContent);
        $this->assertIsInt($responseContent['id']);

        return $responseContent['id'];
    }

    public function testColumnWinner(): void
    {
        $gameId = $this->startGame();

        $this->assertNull($this->postMove($gameId, 1, 0, 0));
        $this->assertNull($this->postMove($gameId, 2, 0, 1));
        $this->assertNull($this->postMove($gameId, 1, 1, 0));
        $this->assertNull($this->postMove($gameId, 2, 0, 2));
        $this->assertSame(1, $this->postMove($gameId, 1, 2, 0));
    }

    public function testRowWinner(): void
    {
        $gameId = $this->startGame();

        $this->assertNull($this->postMove($gameId, 1, 0, 0));
        $this->assertNull($this->postMove($gameId, 2, 1, 0));
        $this->assertNull($this->postMove($gameId, 1, 0, 1));
        $this->assertNull($this->postMove($gameId, 2, 1, 2));
        $this->assertNull($this->postMove($gameId, 1, 2, 2));
        $this->assertSame(2, $this->postMove($gameId, 2, 1, 1));
    }

    public function testFirstDiagonalWinner(): void
    {
        $gameId = $this->startGame();

        $this->assertNull($this->postMove($gameId, 1, 0, 0));
        $this->assertNull($this->postMove($gameId, 2, 1, 0));
        $this->assertNull($this->postMove($gameId, 1, 1, 1));
        $this->assertNull($this->postMove($gameId, 2, 1, 2));
        $this->assertSame(1, $this->postMove($gameId, 1, 2, 2));
    }

    public function testSecondDiagonalWinner(): void
    {
        $gameId = $this->startGame();

        $this->assertNull($this->postMove($gameId, 1, 0, 2));
        $this->assertNull($this->postMove($gameId, 2, 1, 0));
        $this->assertNull($this->postMove($gameId, 1, 1, 1));
        $this->assertNull($this->postMove($gameId, 2, 2, 1));
        $this->assertSame(1, $this->postMove($gameId, 1, 2, 0));
    }
}
