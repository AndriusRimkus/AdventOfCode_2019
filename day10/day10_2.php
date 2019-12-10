<?php declare(strict_types=1);

namespace day10_2;

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

    public function __construct(int $posX, int $posY, string $symbol)
    {
        $this->posX = $posX;
        $this->posY = $posY;
        $this->symbol = $symbol;
    }

    public function __debugInfo()
    {
        return [
            'coords' => "$this->posX, $this->posY",
            'visibleCellCount' => count($this->visibleCells)
        ];
    }

    public function getSortedBlockedAngles(): array
    {
        sort($this->blockedAngles);
        return $this->blockedAngles;
    }

    public function getInfo(): array
    {
        return [$this->posX, $this->posY, $this->symbol];
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

    public function isEqual(MapCell $mapCell): bool
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

    public function getAngleBetweenCells(MapCell $mapCell): float
    {
        return round(rad2deg(atan2($mapCell->posY - $this->posY, $mapCell->posX - $this->posX)), 5);
    }

    public function getDistanceBetweenCells(MapCell $mapCell): float
    {
        $x = (pow($mapCell->posX - $this->posX, 2));
        $y = (pow($mapCell->posY - $this->posY, 2));

        return sqrt($x + $y);
    }

    public function destroy()
    {
        $this->symbol = '.';
    }

    public function getVisibleCellCount(): int
    {
        return count($this->visibleCells);
    }

    public function getPosX(): int
    {
        return $this->posX;
    }

    public function getPosY(): int
    {
        return $this->posY;
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
$station = null;

foreach ($map as $mapRow) {
    /** @var MapCell $mapCell */
    foreach ($mapRow as $mapCell) {
        if ($mapCell->getVisibleCellCount() > $maxVisibleCount) {
            $maxVisibleCount = $mapCell->getVisibleCellCount();
            $station = $mapCell;
        }
    }
}

$blastAsteroid = function (MapCell $station, float $blastDegree) use ($map): ?MapCell {
    foreach ($map as $mapRow) {
        /** @var MapCell[] $sameAngleCells */
        $sameAngleCells = [];

        /** @var MapCell $mapCell */
        foreach ($mapRow as $mapCell) {
            if ($mapCell->isEqual($station) || !$mapCell->isAsteroid()) {
                continue;
            }

            if ($station->getAngleBetweenCells($mapCell) === $blastDegree) {
                $sameAngleCells[] = $mapCell;
            }
        }

        if (empty($sameAngleCells)) {
            continue;
        }

        usort($sameAngleCells, function(MapCell $mapCellA, MapCell $mapCellB) use ($station) {
            return $mapCellA->getDistanceBetweenCells($station) <=> $mapCellB->getDistanceBetweenCells($station);
        });

        $sameAngleCells[0]->destroy();

        return $sameAngleCells[0];
    }

    return null;
};

$blockedAngles = $station->getSortedBlockedAngles();

while (current($blockedAngles) !== -90.0) {
    next($blockedAngles);
}

$blastCount = 0;

while (true) {
    $blastedAsteroid = $blastAsteroid($station, current($blockedAngles));

    if ($blastedAsteroid) {
        $blastCount++;
    }

    if ($blastCount === 200) {
        print($blastedAsteroid->getPosX() * 100 + $blastedAsteroid->getPosY() . PHP_EOL);
        $endTime = microtime(true);
        $execution_time = ($endTime - $startTime);
        print("Execution time: {$execution_time} sec");

        die();
    }

    next($blockedAngles);

    if (current($blockedAngles) === false) {
        reset($blockedAngles);
    }
}
