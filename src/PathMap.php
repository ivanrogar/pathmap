<?php

/**
 * @phpcs:disable PSR1.Files.SideEffects
 */

declare(strict_types=1);

namespace App;

use Exception;

/**
 * Class PathMap
 * @package App
 * @SuppressWarnings(Short)
 */
class PathMap
{
    private ?int $x = null;
    private ?int $y = null;
    private ?string $currentDirection = null;

    private array $letters = [];
    private array $path = [];
    private array $map = [];
    private string $originalMap;

    private array $visitedPositions = [];

    private const SYMBOL_START = '@';
    private const SYMBOL_STOP = 'x';

    private const DIRECTION_LEFT = 'left';
    private const DIRECTION_RIGHT = 'right';
    private const DIRECTION_UP = 'up';
    private const DIRECTION_DOWN = 'down';

    private const OPPOSITES = [
        self::DIRECTION_UP => self::DIRECTION_DOWN,
        self::DIRECTION_DOWN => self::DIRECTION_UP,
        self::DIRECTION_LEFT => self::DIRECTION_RIGHT,
        self::DIRECTION_RIGHT => self::DIRECTION_LEFT,
    ];

    public const ERROR_NO_START_SYMBOL = 'Start symbol not found';
    public const ERROR_NO_END_SYMBOL = 'End symbol not found';

    /**
     * PathMap constructor.
     * @param string $map
     */
    public function __construct(string $map)
    {
        $this->originalMap = $map;
        $this->setup();
    }

    /**
     * @throws Exception
     */
    public function follow()
    {
        if (!$this->x) {
            throw new Exception(self::ERROR_NO_START_SYMBOL);
        } elseif (strpos($this->originalMap, self::SYMBOL_STOP) === false) {
            throw new Exception(self::ERROR_NO_END_SYMBOL);
        }

        $this->path[] = self::SYMBOL_START;

        $this->move();
    }

    /**
     * @return string
     */
    public function getLetters(): string
    {
        return implode('', $this->letters);
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return implode('', $this->path);
    }

    private function move()
    {
        $coordinates = $this->getNewCoordinates();

        if (empty($coordinates)) {
            return;
        }

        list($direction, $x, $y, $positionData) = $coordinates;

        $this->currentDirection = $direction;

        if (
            ctype_upper($positionData) &&
            !in_array("$x,$y", $this->visitedPositions)
        ) {
            $this->letters[] = $positionData;
        }

        $this->path[] = $positionData;

        $this->x = $x;
        $this->y = $y;

        $this->visitedPositions[] = "$x,$y";

        if ($positionData !== self::SYMBOL_STOP) {
            $this->move();
        }
    }

    /**
     * @param mixed $x
     * @param mixed $y
     * @return mixed|null
     */
    private function getByCoordinates($x, $y)
    {
        if (array_key_exists($y, $this->map) && array_key_exists($x, $this->map[$y])) {
            return $this->map[$y][$x];
        }

        return null;
    }

    /**
     * @return mixed
     * @SuppressWarnings(Complexity)
     */
    private function getNewCoordinates()
    {
        $directions = [];

        $movements = [
            [self::DIRECTION_UP, $this->x, $this->y - 1],
            [self::DIRECTION_DOWN, $this->x, $this->y + 1],
            [self::DIRECTION_LEFT, $this->x - 1, $this->y],
            [self::DIRECTION_RIGHT, $this->x + 1, $this->y],
        ];

        $opposites = self::OPPOSITES;

        foreach ($movements as $movement) {
            list($direction, $x, $y) = $movement;

            if ($this->currentDirection) {
                if (
                    $opposites[$this->currentDirection] === $direction
                ) {
                    continue;
                }
            }

            $positionData = $this->getByCoordinates($x, $y);

            $priority = 0;

            if (trim((string)$positionData)) {
                switch ($direction) {
                    case self::DIRECTION_LEFT:
                        $priority = 5;
                        break;
                    case self::DIRECTION_RIGHT:
                        $priority = 10;
                        break;
                    case self::DIRECTION_UP:
                        $priority = 15;
                        break;
                    case self::DIRECTION_DOWN:
                        $priority = 20;
                        break;
                }

                if ($direction === $this->currentDirection) {
                    $priority = 0;
                }

                $directions[] = [
                    $direction, $x, $y, $positionData, $priority
                ];
            }
        }

        usort($directions, function ($first, $second) {
            return $first[4] <=> $second[4];
        });

        reset($directions);

        return current($directions);
    }

    private function setup()
    {
        $map = $this->originalMap;

        $this->letters = [];
        $this->path = [];
        $this->map = [];
        $this->visitedPositions = [];

        $rows = explode(PHP_EOL, $map);

        foreach ($rows as $rowIndex => $row) {
            $newRow = [];

            foreach (str_split($row) as $charIndex => $char) {
                if ($char === self::SYMBOL_START) {
                    $this->x = $charIndex;
                    $this->y = $rowIndex;
                }

                $newRow[] = $char;
            }

            $this->map[] = $newRow;
        }
    }
}
