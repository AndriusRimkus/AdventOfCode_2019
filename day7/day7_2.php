<?php declare(strict_types=1);

$memory = file_get_contents(__DIR__.'/input');
$memory = array_map('intval', explode(',', $memory));

function getComputerOutput(array $memory, int $pointer, array $input): ?array {
    for ($i = $pointer; $i < count($memory); $i++) {
        $code = $memory[$i];

        $opCode = (int) ($code % 100);

        if ($opCode === 4) {
            return [
                'output' => $memory[$memory[$i + 1]],
                'memory' => [
                    'memory' => $memory,
                    'pointer' => $i + 2
                ]
            ];
        }

        if ($opCode === 3) {
            $storage = &$memory[$memory[$i + 1]];
            $storage = array_shift($input);

            $i += 1;
            continue;
        }

        if ($opCode === 99) {
            throw new \Exception('Halt');
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

    return [
        'output' => null,
        'memory' => [
            'memory' => $memory,
            'pointer' => null
        ]
    ];
};

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

foreach (permute([5, 6, 7, 8, 9]) as $phaseSequence) {
    $nodeMemories = array_fill(0, 5, [
        'memory' => $memory,
        'pointer' => 0
    ]);

    $nodeOutput = null;
    $lastNodeOutput = null;

    for ($i = 0; ; $i++) {
        $nodeIndex = $i % count($phaseSequence);
        $phaseSetting = $phaseSequence[$nodeIndex];
        $nodeMemory = $nodeMemories[$nodeIndex];

        if ($i < count($phaseSequence)) {
            $nodeInput = [$phaseSetting, $nodeOutput ?? 0];
        } else {
            $nodeInput = [$nodeOutput ?? 0];
        }

        try {
            $result = getComputerOutput($nodeMemory['memory'], $nodeMemory['pointer'], $nodeInput);

            $nodeMemories[$nodeIndex] = $result['memory'];
            $nodeOutput = $result['output'];
        } catch (\Exception $e) {

            $sequenceOutputs[] = $lastNodeOutput;
            continue 2;
        }

        if ($nodeIndex === count($phaseSequence) - 1) {
            $lastNodeOutput = $nodeOutput;
        }
    }
}

print(max($sequenceOutputs));
