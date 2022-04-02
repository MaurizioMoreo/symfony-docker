<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Webmozart\Assert\Assert;

#[ORM\Entity]
class Game
{
    private const TABLE_SIZE = 3;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    public readonly int $id;

    #[ORM\Column(type: "json", name: "table_data")]
    private array $table;

    #[ORM\Column(type: "integer")]
    private int $nextPlayer = 1;

    public ?int $winner = null;

    public function __construct()
    {
        for ($i = 0; $i < self::TABLE_SIZE; $i++) {
            for ($j = 0; $j < self::TABLE_SIZE; $j++) {
                $this->table[$i][$j] = null;
            }
        }
    }

    public function move(int $player, int $horizontal, int $vertical): void
    {
        Assert::null($this->winner);
        Assert::same($player, $this->nextPlayer);

        Assert::greaterThanEq($horizontal, 0);
        Assert::lessThanEq($horizontal, self::TABLE_SIZE - 1);

        Assert::greaterThanEq($vertical, 0);
        Assert::lessThanEq($vertical, self::TABLE_SIZE - 1);

        Assert::null($this->table[$horizontal][$vertical]);

        $this->table[$horizontal][$vertical] = $player;
        $this->nextPlayer = $this->nextPlayer == 1 ? 2 : 1;
        $this->checkWinner();
    }

    private function checkWinner(): void
    {
        $this->winner = $this->checkDiagonals()
            ?? $this->checkRows()
            ?? $this->checkColumns();
    }

    private function checkDiagonals(): ?int
    {
        return $this->checkFirstDiagonal() ?? $this->checkSecondDiagonal();
    }

    private function checkFirstDiagonal(): ?int
    {
        $player = $this->table[0][0];

        if (null === $player) {
            return null;
        }

        for ($i = 1; $i < self::TABLE_SIZE; $i++) {
            if ($player !== $this->table[$i][$i]) {
                return null;
            }
        }

        return $player;
    }

    private function checkSecondDiagonal(): ?int
    {
        $maxVertical = self::TABLE_SIZE - 1;
        $player = $this->table[0][$maxVertical];

        if (null === $player) {
            return null;
        }

        for ($i = 1; $i < self::TABLE_SIZE; $i++) {
            if ($player !== $this->table[$i][$maxVertical - $i]) {
                return null;
            }
        }

        return $player;
    }

    private function checkRows(): ?int
    {
        for ($i = 0; $i < self::TABLE_SIZE; $i++) {
            $player = $this->checkRow($i);
            if (null !== $player) {
                return $player;
            }
        }

        return null;
    }

    private function checkColumns(): ?int
    {
        for ($i = 0; $i < self::TABLE_SIZE; $i++) {
            $player = $this->checkColumn($i);
            if (null !== $player) {
                return $player;
            }
        }

        return null;
    }

    private function checkRow(int $rowNumber): ?int
    {
        $player = $this->table[$rowNumber][0];
        if (null === $player) {
            return null;
        }

        for ($i = 1; $i < self::TABLE_SIZE; $i++) {
            if ($this->table[$rowNumber][$i] !== $player) {
                return null;
            }
        }

        return $player;
    }

    private function checkColumn(int $colNumber): ?int
    {
        $player = $this->table[0][$colNumber];
        if (null === $player) {
            return null;
        }

        for ($i = 1; $i < self::TABLE_SIZE; $i++) {
            if ($this->table[$i][$colNumber] !== $player) {
                return null;
            }
        }

        return $player;
    }

    public function table(): string
    {
        return \Safe\json_encode($this->table);
    }
}
