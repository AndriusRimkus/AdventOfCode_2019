<?php declare(strict_types=1);

require_once('../Computer/Computer.php');

use Common\Computer;

$startTime = microtime(true);

const TILE_EMPTY = 0;
const TILE_WALL = 1;
const TILE_BLOCK = 2;
const TILE_PADDLE = 3;
const TILE_BALL = 4;

$tileTexture = [
    TILE_EMPTY => ' ',
    TILE_WALL => '█',
    TILE_BLOCK => '░',
    TILE_PADDLE => '═',
    TILE_BALL => 'O'
];

$memory = file_get_contents(__DIR__.'/input_1');
$memory = array_map('intval', explode(',', $memory));

$map = [];
$computer = new Computer($memory, []);

while ($output = $computer->getNextOutput(3)) {
    [$x, $y, $tileType] = $output;

    if (!isset($map[$y])) {
        $map[$y] = [];
    }

    $map[$y][$x] = $tileType;
}

$count = 0;

foreach ($map as $row) {
    foreach ($row as $tile) {
        print($tileTexture[$tile]);

        if ($tile === TILE_BLOCK) {
            $count++;
        }
    }

    print(PHP_EOL);
}

print('Tile count: ' . $count . PHP_EOL);

$endTime = microtime(true);
$execution_time = ($endTime - $startTime);
print("Execution time: {$execution_time} sec");


