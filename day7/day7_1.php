<?php declare(strict_types=1);

$memory = file_get_contents(__DIR__.'/input');
$memory = explode(',', $memory);

function getComputerOutput(array $memory, array $input): ?int {
    for ($i = 0; $i < count($memory); $i++) {
        $code = $memory[$i];

        $opCode = (int) ($code % 100);

        if ($opCode === 4) {
            return $memory[$memory[$i + 1]];
        }

        if ($opCode === 3) {
            $storage = &$memory[$memory[$i + 1]];
            $storage = array_shift($input);

            $i += 1;
            continue;
        }

        if ($opCode === 99) {
            die('halt');
        }

        $paramModes = (int) ($code / 100);

        // 0 - position mode
        // 1 - immediate

        $param1Mode = $paramModes % 10;
        $param2Mode = (int) ($paramModes / 10);

        $param1 = $memory[$i + 1];
        $param2 = $memory[$i + 2];

        if ($param1Mode === 0) {
            $param1 = $memory[$param1];
        }

        if ($param2Mode === 0) {
            $param2 = $memory[$param2];
        }

        if (in_array($opCode, [1, 2, 7, 8])) {
            $storage = &$memory[$memory[$i + 3]];
        }

        if ($opCode === 1) {
            $storage = $param1 + $param2;
            $i += 3;
        }

        if ($opCode === 2) {
            $storage = $param1 * $param2;
            $i += 3;
        }

        if ($opCode === 5) {
            $i = $param1 !== 0 ? $param2 - 1 : $i + 2;
        }

        if ($opCode === 6) {
            $i = $param1 === 0 ? $param2 - 1 : $i + 2;
        }

        if ($opCode === 7) {
            $storage = $param1 < $param2 ? 1 : 0;
            $i += 3;
        }

        if ($opCode === 8) {
            $storage = $param1 === $param2 ? 1 : 0;
            $i += 3;
        }
    }

    return null;
};

$phaseSequence = [1, 0, 4, 3, 2];
$nodeOutput = null;

function permute(array $items, array $perms = [], array &$result = []): array
{
    if (empty($items)) {
        $result[] = $perms;
    } else {
        for ($i = count($items) - 1; $i >= 0; --$i) {
            $newItems = $items;
            $newPerms = $perms;
            list($foo) = array_splice($newItems, $i, 1);
            array_unshift($newPerms, $foo);
            permute($newItems, $newPerms, $result);
        }
    }

    return $result;
}

$sequenceOutputs = [];

foreach (permute([0, 1, 2, 3, 4]) as $phaseSequence) {
    $nodeOutput = null;

    foreach ($phaseSequence as $phaseSetting) {
        $nodeOutput = getComputerOutput($memory, [$phaseSetting, $nodeOutput ?? 0]);
    }

    $sequenceOutputs[] = $nodeOutput;
}


print(max($sequenceOutputs));
