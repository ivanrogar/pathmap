<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use App\PathMap;

/**
 * Class PathMapTest
 * @covers \App\PathMap
 */
class PathMapTest extends TestCase
{
    private array $maps = [];

    public function setUp(): void
    {
        foreach (range(1, 3) as $item) {
            $this->maps[] = \file_get_contents(
                dirname(__FILE__)
                . DIRECTORY_SEPARATOR
                . '..'
                . DIRECTORY_SEPARATOR
                . 'maps'
                . DIRECTORY_SEPARATOR
                . "map$item.txt"
            );
        }
    }

    /**
     * @test
     * @throws Exception
     */
    public function test_predefined_maps()
    {
        $expected = [
            ['ACB', '@---A---+|C|+---+|+-B-x'],
            ['ABCD', '@|A+---B--+|+----C|-||+---D--+|x'],
            ['BEEFCAKE', '@---+B||E--+|E|+--F--+|C|||A--|-----K|||+--E--Ex'],
        ];

        foreach ($this->maps as $index => $map) {
            $pathMap = new PathMap($map);

            $pathMap->follow();

            list($expectedLetters, $expectedPath) = $expected[$index];

            $this->assertEquals($expectedLetters, $pathMap->getLetters());

            $this->assertEquals($expectedPath, $pathMap->getPath());
        }
    }

    /**
     * @test
     * @throws Exception
     */
    public function should_throw_on_no_start_symbol()
    {
        $this->expectExceptionMessage(PathMap::ERROR_NO_START_SYMBOL);

        $pathMap = new PathMap('');

        $pathMap->follow();
    }

    /**
     * @test
     * @throws Exception
     */
    public function should_throw_on_no_end_symbol()
    {
        $this->expectExceptionMessage(PathMap::ERROR_NO_END_SYMBOL);

        $pathMap = new PathMap(<<<EOF
  @---A---+
          |
  -B-+   C
      |   |
      +---+
EOF
);

        $pathMap->follow();
    }
}
