<?php declare(strict_types=1);

$memory = file_get_contents(__DIR__.'/input');
$memory = array_map('intval', explode(',', $memory));

$relativeBase = 0;

$getParamPointers = function (int $pointer, int $code) use (&$memory, &$relativeBase): array {
    $paramModes = (int)($code / 100);

    // 0 - position mode
    // 1 - immediate
    // 2 - relative

    $param1Mode = $paramModes % 10;
    $param2Mode = (int)($paramModes % 100 / 10);
    $param3Mode = (int)($paramModes / 100);

    $param1 = $pointer + 1;
    $param2 = $pointer + 2;
    $param3 = $pointer + 3;

    if ($param1Mode === 0) {
        $param1 = $memory[$param1] ?? 0;
    }

    if ($param2Mode === 0) {
        $param2 = $memory[$param2] ?? 0;
    }

    if ($param3Mode === 0) {
        $param3 = $memory[$param3] ?? 0;
    }

    if ($param1Mode === 2) {
        $param1 = $relativeBase + $memory[$param1] ?? 0;
    }

    if ($param2Mode === 2) {
        $param2 = $relativeBase + $memory[$param2] ?? 0;
    }

    if ($param3Mode === 2) {
        $param3 = $relativeBase + $memory[$param3] ?? 0;
    }

    return [$param1, $param2, $param3];
};

$getParamValues = function (int $pointer, int $code) use (&$memory, &$relativeBase, $getParamPointers): array {
    [$param1Pointer, $param2Pointer, $param3Pointer] = $getParamPointers($pointer, $code);

    return [$memory[$param1Pointer] ?? 0, $memory[$param2Pointer] ?? 0, $memory[$param3Pointer] ?? 0];
};

$getComputerOutput = function (int $pointer, array $input) use (&$memory, &$relativeBase, $getParamPointers, $getParamValues) {

    for ($i = $pointer; $i < count($memory); $i++) {
        $code = $memory[$i];

        $opCode = (int)($code % 100);

        if ($opCode === 99) {
            throw new Exception('Halt');
        }

        [$param1, $param2, $param3] = $getParamValues($i, $code);
        [$param1Pointer, $param2Pointer, $param3Pointer] = $getParamPointers($i, $code);

        if (in_array($opCode, [1, 2, 7, 8])) {
            $storage = &$memory[$param3Pointer];
        }

        if ($opCode === 1) {
            $storage = $param1 + $param2;
            $i += 3;
        }

        if ($opCode === 2) {
            $storage = $param1 * $param2;
            $i += 3;
        }

        if ($opCode === 3) {
            $memory[$param1Pointer] = array_shift($input);
            $i += 1;
        }

        if ($opCode === 4) {
            print_r('OUTPUT: '.$param1.PHP_EOL);
            $i += 1;
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

        if ($opCode === 9) {
            $relativeBase += $param1;
            $i += 1;
        }
    }
};

$getComputerOutput(0, [2]);
