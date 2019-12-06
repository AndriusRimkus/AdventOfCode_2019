<?php

$input = file_get_contents(__DIR__ . '/input');
$input = explode(PHP_EOL, $input);

$orbits = [];

foreach ($input as $orbitInfo) {
    [$parent, $child] = explode(')', $orbitInfo);
    $orbits[$child] = $parent;
}

$orbitCount = 0;

foreach ($orbits as $child => $parent) {
    $orbitCount++;

    while ($parent !== 'COM') {
        $parent = $orbits[$parent];

        $orbitCount++;
    }
}

print($orbitCount);