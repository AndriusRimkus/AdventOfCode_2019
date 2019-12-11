<?php declare(strict_types=1);
require_once('Computer.php');

$startTime = microtime(true);

$memory = file_get_contents(__DIR__.'/input');
$memory = array_map('intval', explode(',', $memory));

$movement = [
    0 => [0 , -1], // top
    1 => [1, 0], // right
    2 => [0, 1], // bottom
    3 => [-1, 0] // left
];

$direction = 0;
$pos = [0, 0];
$hull = [];

$computer = new Computer($memory, [1]);
$generator = $computer->getComputerOutput();

$paintHull = function(int $x, int $y, int $color) use (&$hull) {
    if (!isset($hull[$y])) {
        $hull[$y] = [];
    }

    $hull[$y][$x] = $color;
};

try {
    while (true) {
        $color = $generator->current();
        $generator->next();
        $turn = $generator->current();

        $paintHull($pos[0], $pos[1], $color);

        if ($turn === 1) {
            $direction = ($direction + 1) % 4;
        } else {
            $direction -= 1;
            $direction = $direction < 0 ? 3 : $direction;
        }

        $pos = [$pos[0] + $movement[$direction][0], $pos[1] + $movement[$direction][1]];
        $newColor = $hull[$pos[1]][$pos[0]] ?? 0;

        $computer->loadInput([$newColor]);

        $generator->next();
    }
} catch (\Exception $e) {

}

$count = 0;

$maxX = PHP_INT_MIN;
$maxY = PHP_INT_MIN;
$minX = PHP_INT_MAX;
$minY = PHP_INT_MAX;

foreach ($hull as $y => $row) {
    foreach ($row as $x => $cell) {
        $count++;

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

for ($i = $minY; $i <= $maxY; $i++) {
    for ($j = $minX; $j <= $maxX; $j++) {
        $color = $hull[$i][$j] ?? 0;
        print($color === 0 ? '░' : '█');
    }

    print PHP_EOL;
}

$endTime = microtime(true);
$execution_time = ($endTime - $startTime);
print("Execution time: {$execution_time} sec");
