<?php declare(strict_types=1);

require_once('../Computer/Computer.php');

use Common\Computer;

$startTime = microtime(true);

const TILE_EMPTY = 0;
const TILE_WALL = 1;
const TILE_BLOCK = 2;
const TILE_PADDLE = 3;
const TILE_BALL = 4;

const JOYSTICK_NEUTRAL = 0;
const JOYSTICK_LEFT = -1;
const JOYSTICK_RIGHT = 1;

$tileTextures = [
    TILE_EMPTY => ' ',
    TILE_WALL => '█',
    TILE_BLOCK => '░',
    TILE_PADDLE => '═',
    TILE_BALL => 'O'
];

$memory = file_get_contents(__DIR__.'/input_2');
$memory = array_map('intval', explode(',', $memory));

$map = [];

$computer = new Computer($memory, []);

$paintTheMap = function () use (&$map, $tileTextures) {
    $minY = 100;
    $maxY = 0;
    $minX = 100;
    $maxX = 0;

    foreach ($map as $y => $row) {
        foreach ($row as $x => $tile) {
            if ($x < $minX) {
                $minX = $x;
            }
            if ($x > $maxX) {
                $maxX = $x;
            }
            if ($y < $minY) {
                $minY = $y;
            }
            if ($y > $maxY) {
                $maxY = $y;
            }
        }
    }

    for ($y = $minY; $y <= $maxY; $y++) {
        for ($x = $minX; $x <= $maxX; $x++) {
            print($tileTextures[$map[$y][$x] ?? TILE_EMPTY]);
        }

        print(PHP_EOL);
    }
};

$determinePosition = function (int $object) use (&$map): array {
    foreach ($map as $y => $row) {
        foreach ($row as $x => $tile) {
            if ($tile === $object) {
                return [$x, $y];
            }
        }
    }

    return [-1, -1];
};

$determineInput = function () use (&$map, $determinePosition): int {
    $ballPos = $determinePosition(TILE_BALL);
    $paddlePos = $determinePosition(TILE_PADDLE);

    if ($ballPos[0] < $paddlePos[0]) {
        return JOYSTICK_LEFT;
    } elseif ($ballPos[0] > $paddlePos[0]) {
        return JOYSTICK_RIGHT;
    }

    return JOYSTICK_NEUTRAL;
};

while (true) {
    $output = $computer->getNextOutput(3);

    if (is_null($output)) {
        $paintTheMap();

        break;
    }

    if ($output === 'INPUT_NEEDED') {
        $paintTheMap();

        $input = $determineInput();
        var_dump('GIVEN INPUT: ' . $input . PHP_EOL);

        $computer->loadInput([$input]);

        $map = [];
        continue;
    }

    [$x, $y, $tileType] = $output;

    if ($x === -1) {
        print('SCORE: '.$tileType.PHP_EOL);
        continue;
    }

    if (!isset($map[$y])) {
        $map[$y] = [];
    }

    $map[$y][$x] = $tileType;
}

$endTime = microtime(true);
$execution_time = ($endTime - $startTime);
print("Execution time: {$execution_time} sec");


