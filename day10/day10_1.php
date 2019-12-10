<?php declare(strict_types=1);

namespace day10_1;

$startTime = microtime(true);

$map = trim(file_get_contents(__DIR__.'/input'));
$map = preg_split('/\r\n|\r|\n/', $map);
$rawMap = array_map('str_split', $map);
$map = [];

foreach ($rawMap as $posY => $mapRow) {
    $map[$posY] = [];

    foreach ($mapRow as $posX => $symbol) {
        $map[$posY][$posX] = new MapCell($posX, $posY, $symbol);
    }
}

class MapCell
{
    private $posX;
    private $posY;
    private $symbol;
    private $visibleCells = [];
    private $blockedAngles = [];

    public function __debugInfo()
    {
        return [
            'coords' => "$this->posX, $this->posY",
            'visibleCellCount' => count($this->visibleCells)
        ];
    }

    public function __construct(int $posX, int $posY, string $symbol)
    {
        $this->posX = $posX;
        $this->posY = $posY;
        $this->symbol = $symbol;
    }

    public function relateWithMapCell(MapCell $mapCell)
    {
        if ($this->isEqual($mapCell) || !$mapCell->isAsteroid()) {
            return;
        }

        if ($this->isCellVisible($mapCell)) {
            $this->visibleCells[] = $mapCell;
            $this->blockedAngles[] = $this->getAngleBetweenCells($mapCell);
        }
    }

    public function getVisibleCellCount(): int
    {
        return count($this->visibleCells);
    }

    private function isEqual(MapCell $mapCell): bool
    {
        return $this->posX === $mapCell->posX && $this->posY === $mapCell->posY;
    }

    public function isAsteroid(): bool
    {
        return $this->symbol === '#';
    }

    private function isCellVisible(MapCell $mapCell): bool
    {
        $angle = $this->getAngleBetweenCells($mapCell);

        foreach ($this->blockedAngles as $blockedAngle) {
            if ($angle === $blockedAngle) {
                return false;
            }
        }

        return true;
    }

    private function getAngleBetweenCells(MapCell $mapCell): float
    {
        return round(rad2deg(atan2($mapCell->posY - $this->posY, $mapCell->posX - $this->posX)), 5);
    }
}

$processMapCell = function (MapCell $mapCell) use ($map) {
    if (!$mapCell->isAsteroid()) {
        return;
    }

    foreach ($map as $mapRow) {

        /** @var MapCell $mapCell */
        foreach ($mapRow as $mapCellToCheck) {
            $mapCell->relateWithMapCell($mapCellToCheck);
        }
    }
};

foreach ($map as $mapRow) {
    foreach ($mapRow as $mapCell) {
        $processMapCell($mapCell);
    }
}

$maxVisibleCount = 0;

foreach ($map as $mapRow) {
    /** @var MapCell $mapCell */
    foreach ($mapRow as $mapCell) {
        if ($mapCell->getVisibleCellCount() > $maxVisibleCount) {
            $maxVisibleCount = $mapCell->getVisibleCellCount();
        }
    }
}

print($maxVisibleCount . PHP_EOL);

$endTime = microtime(true);
$execution_time = ($endTime - $startTime);
print("Execution time: {$execution_time} sec");
