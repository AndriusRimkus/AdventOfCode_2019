<?php declare(strict_types=1);

$startTime = microtime(true);

$conversions = file_get_contents(__DIR__.'/input');
$conversions = preg_split('/\r\n|\r|\n/', trim($conversions));

function constructElement(string $element)
{
    preg_match('/(\d+) (.*)/', $element, $matches);

    return [$matches[2] => (int)$matches[1]];
}

$result = [];

foreach ($conversions as $equation) {
    [$input, $output] = explode(' => ', $equation);
    $output = constructElement($output);

    $input = explode(',', $input);
    $input = array_map('trim', $input);

    $inputMap = [];

    foreach ($input as $inputDesc) {
        $inputEl = constructElement($inputDesc);
        $inputMap[key($inputEl)] = current($inputEl);
    }

    $result[key($output)] = [
        'outputQuantity' => current($output),
        'input' => $inputMap
    ];
}

$conversions = $result;

$elStorage = array_combine(array_keys($conversions), array_fill(0, count($conversions), 0));
$elStorage['ORE'] = 1000000000000;

$buildElement = function (string $elName, int $quantityAsked, array $conversions) use (
    &$elStorage,
    &$buildElement
) {
    if ($elName === 'ORE') {
        if ($elStorage['ORE'] - $quantityAsked < 0) {
            throw new Exception();
        }

        $elStorage['ORE'] -= $quantityAsked;

        return;
    }

    $input = $conversions[$elName]['input'];

    $outputQuantity = $conversions[$elName]['outputQuantity'];

    $batchCoef = (int)(ceil(($quantityAsked - $elStorage[$elName]) / $outputQuantity));

    if ($elStorage[$elName] >= $quantityAsked) {
        $elStorage[$elName] -= $quantityAsked;

        return;
    }

    $quantityMade = $elStorage[$elName];
    $elStorage[$elName] = 0;

    foreach ($input as $inputName => $inputQ) {
        $buildElement($inputName, $inputQ * $batchCoef, $conversions);
    }

    $quantityMade += $outputQuantity * $batchCoef;

    $elStorage[$elName] += ($quantityMade - $quantityAsked);
};

$count = 0;
$totalCount = 0;

$batchSize = 1000000000000;

$properConv = $conversions;

while (true) {
    $elStorageClone = $elStorage;

    try {
        $buildElement('FUEL', $batchSize, $properConv);
    } catch (Exception $e) {
        $totalCount += $batchSize * $count;

        $elStorage = $elStorageClone;

        if ($batchSize === 1) {
            break;
        }

        $count = 0;
        $batchSize = (int)($batchSize / 10);
        $properConv = $conversions;

        continue;
    }

    $count++;
}

print('Total count: ' . $totalCount . PHP_EOL);

$endTime = microtime(true);
$executionTime = ($endTime - $startTime);
print("Execution time: {$executionTime} sec");


