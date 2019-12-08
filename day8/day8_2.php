<?php declare(strict_types=1);

$input = trim(file_get_contents(__DIR__.'/input'));
$screens = str_split($input, 25 * 6);
$screensReversed = array_reverse($screens);

$visualScreen = array_fill(0, 6, array_fill(0, 25, ' '));

foreach ($screensReversed as $screen) {
    foreach (str_split($screen) as $index => $pixel) {
        $line = (int)($index / 25);
        $column = $index % 25;

        if ($pixel === '0') {
            $visualScreen[$line][$column] = '░';
        }

        if ($pixel === '1') {
            $visualScreen[$line][$column] = '█';
        }
    }
}

foreach ($visualScreen as $visualLine) {
    print join($visualLine) . PHP_EOL;
}
