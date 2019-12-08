<?php declare(strict_types=1);

$input = trim(file_get_contents(__DIR__.'/input'));
$screens = str_split($input, 25 * 6);
$minZeroCount = PHP_INT_MAX;

$minScreenOneCount = 0;
$minScreenTwoCount = 0;

foreach ($screens as $screen) {
    $screenZeroCount = count(preg_split('/0/', $screen)) - 1;
    if ($screenZeroCount < $minZeroCount) {
        $minZeroCount = $screenZeroCount;

        $minScreenOneCount = count(preg_split('/1/', $screen)) - 1;
        $minScreenTwoCount = count(preg_split('/2/', $screen)) - 1;
    }
}

print($minScreenOneCount * $minScreenTwoCount);
