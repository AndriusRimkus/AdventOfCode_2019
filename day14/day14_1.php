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

$conversions['ORE'] = [
    'outputQuantity' => 1,
    'input' => []
];

$elStorage = array_combine(array_keys($conversions), array_fill(0, count($conversions), 0));
$elUsage = array_combine(array_keys($conversions), array_fill(0, count($conversions), 0));

$buildElement = function (string $elName, int $quantityAsked) use (&$elStorage, &$elUsage, $conversions, &$buildElement) {
    $input = $conversions[$elName]['input'];
    $outputQuantity = $conversions[$elName]['outputQuantity'];

    if ($elStorage[$elName] >= $quantityAsked) {
        $elStorage[$elName] -= $quantityAsked;
        $elUsage[$elName] += $quantityAsked;

        return;
    }

    $quantityMade = $elStorage[$elName];
    $elStorage[$elName] = 0;

    while($quantityMade < $quantityAsked) {
        foreach ($input as $inputName => $inputQ) {
            $buildElement($inputName, $inputQ);
        }

        $quantityMade += $outputQuantity;
    }

    $elStorage[$elName] += $quantityMade - $quantityAsked;
    $elUsage[$elName] += $quantityAsked;
};

$buildElement('FUEL', 1);

print_r($elUsage);
print_r($elStorage);

$endTime = microtime(true);
$executionTime = ($endTime - $startTime);
print("Execution time: {$executionTime} sec");


