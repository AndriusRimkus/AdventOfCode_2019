<?php

$input = file_get_contents(__DIR__ . '/input');
$input = explode(PHP_EOL, $input);

$orbits = [];

foreach ($input as $orbitInfo) {
    [$parent, $child] = explode(')', $orbitInfo);
    $orbits[$child] = $parent;
}

$getOrbitChainInfo = function (array $orbits, string $startPoint): array {
    $chainInfo = [];

    foreach ($orbits as $child => $parent) {
        if ($child !== $startPoint) {
            continue;
        }

        $distance = 0;

        while ($parent !== 'COM') {
            $chainInfo[$parent] = $distance;
            $parent = $orbits[$parent];

            $distance++;
        }
    }

    return $chainInfo;
};

$orbitChainInfoYou = $getOrbitChainInfo($orbits, 'YOU');
$orbitChainInfoSan = $getOrbitChainInfo($orbits, 'SAN');

foreach ($orbitChainInfoYou as $starName => $distance) {
    if (isset($orbitChainInfoSan[$starName])) {
        print($distance + $orbitChainInfoSan[$starName]);
        die();
    }
}