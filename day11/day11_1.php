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

$computer = new Computer($memory, [0]);
$generator = $computer->getComputerOutput();

try {
    while (true) {
        $color = $generator->current();
        $generator->next();
        $turn = $generator->current();

        $hull[$pos[0] * 100 + $pos[1]] = $color;

        if ($turn === 1) {
            $direction = ($direction + 1) % 4;
        } else {
            $direction -= 1;
            $direction = $direction < 0 ? 3 : $direction;
        }

        $pos = [$pos[0] + $movement[$direction][0], $pos[1] + $movement[$direction][1]];
        $newColor = $hull[$pos[0] * 100 + $pos[1]] ?? 0;

        $computer->loadInput([$newColor]);

        $generator->next();
    }
} catch (\Exception $e) {

}

print(count($hull) . PHP_EOL);

$endTime = microtime(true);
$execution_time = ($endTime - $startTime);
print("Execution time: {$execution_time} sec");
