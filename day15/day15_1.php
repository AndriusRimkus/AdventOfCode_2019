<?php declare(strict_types=1);
require_once('../Computer/Computer.php');

use Common\Computer;

$startTime = microtime(true);

$memory = file_get_contents(__DIR__.'/input');
$memory = array_map('intval', explode(',', $memory));

const TILE_WALL = 0;
const TILE_EMPTY = 1;
const TILE_TARGET = 2;

const NORTH = 1;
const SOUTH = 2;
const WEST = 3;
const EAST = 4;

$directionCoefs = [
    NORTH => [0, -1],
    SOUTH => [0, 1],
    WEST => [-1, 0],
    EAST => [1, 0]
];

$oppositeDirections = [
    NORTH => SOUTH,
    SOUTH => NORTH,
    WEST => EAST,
    EAST => WEST
];

$computer = new Computer($memory, []);
$map = [];
$normalizedMap = [];

$formMap = function (Node $node) use (&$map) {
    if (!isset($map[$node->getY()])) {
        $map[$node->getY()] = [];
    }

    $map[$node->getY()][$node->getX()] = $node;
};

$getNormalizedMap = function () use (&$map): array {
    $normalizedMap = [];

    $minY = PHP_INT_MAX;
    $maxY = PHP_INT_MIN;
    $minX = PHP_INT_MAX;
    $maxX = PHP_INT_MIN;

    foreach ($map as $y => $row) {
        foreach ($row as $x => $tile) {
            if ($x < $minX) {
                $minX = $x;
            }
            if ($x > $maxX) {
                $maxX = $x;
            }
            if ($y < $minY) {
                $minY = $y;
            }
            if ($y > $maxY) {
                $maxY = $y;
            }
        }
    }

    for ($i = $minY; $i <= $maxY; $i++) {
        for ($j = $minX; $j <= $maxX; $j++) {

            $node = $map[$i][$j] ?? null;

            if (!isset($normalizedMap[$i])) {
                $normalizedMap[$i] = [];
            }

            $normalizedMap[$i][$j] = $node;
        }
    }

    return $normalizedMap;
};

$paintMap = function () use (&$normalizedMap) {
    foreach ($normalizedMap as $y => $row) {
        foreach ($row as $x => $tile) {
            if (is_null($tile)) {
                print(' '."\t");
            } else {
                if ($tile->getX() === 0 && $tile->getY() === 0) {
                    print('S'."\t");
                } elseif ($tile->getType() === TILE_TARGET) {
                    print('*'."\t");
                } elseif ($tile->getType() === TILE_WALL) {
                    print('█'."\t");
                } elseif ($tile->getType() === TILE_EMPTY) {
                    print('░'."\t");
                } else {
                    print($tile->getType()."\t");
                }
            }
        }
        print(PHP_EOL);
    }
};

$pos = [0, 0];

class Node
{
    private $x;

    private $y;

    private $type;

    private $distance = PHP_INT_MAX;

    private $cameFromNode = null;

    private $cameFromDirection = null;

    private $neighbors = [
        NORTH => null,
        SOUTH => null,
        WEST => null,
        EAST => null
    ];

    public function __construct(int $x, int $y, int $type)
    {
        $this->x = $x;
        $this->y = $y;
        $this->type = $type;

        $this->distance = abs($x) + abs($y);
    }

    public function getPos(): array
    {
        return [$this->x, $this->y];
    }

    public function getDistance(): int
    {
        return $this->distance;
    }

    public function __toString(): string
    {
        return "Own info: $this->x, $this->y, $this->type ".PHP_EOL;
    }

    public function setNeighbor(int $direction, Node $neighbor)
    {
        $this->neighbors[$direction] = $neighbor;
    }

    public function getNeighbors(): array
    {
        return $this->neighbors;
    }

    public function getX(): int
    {
        return $this->x;
    }

    public function getY(): int
    {
        return $this->y;
    }

    public function getType(): int
    {
        return $this->type;
    }

    public function setCameFrom(Node $fromNode, int $fromDirection)
    {
        $this->cameFromNode = $fromNode;
        $this->cameFromDirection = $fromDirection;
    }

    public function getCameFromDirection(): ?int
    {
        return $this->cameFromDirection;
    }

    public function getCameFromNode(): ?Node
    {
        return $this->cameFromNode;
    }
}

$currentNode = new Node(0, 0, TILE_EMPTY);
$target = null;
$formMap($currentNode);

$computer->getNextOutput(1);

// Discover map
while (true) {
    foreach ($currentNode->getNeighbors() as $direction => $neighbor) {
        if (is_null($neighbor)) {
            $neighborX = $currentNode->getX() + $directionCoefs[$direction][0];
            $neighborY = $currentNode->getY() + $directionCoefs[$direction][1];

            $computer->loadInput([$direction]);
            $output = $computer->getNextOutput(1)[0];

            $neighbor = new Node($neighborX, $neighborY, $output);
            $neighbor->setCameFrom($currentNode, $oppositeDirections[$direction]);
            $formMap($neighbor);

            $currentNode->setNeighbor($direction, $neighbor);
            $neighbor->setNeighbor($oppositeDirections[$direction], $currentNode);

            if ($neighbor->getType() === TILE_TARGET) {
                $target = $neighbor;
            }

            if ($neighbor->getType() === TILE_EMPTY || $neighbor->getType() === TILE_TARGET) {
                $currentNode = $neighbor;
                continue 2;
            }
        }
    }

    if (is_null($currentNode->getCameFromNode())) {
        break;
    }

    // If all directions are visited, backtrack
    $computer->loadInput([$currentNode->getCameFromDirection()]);
    $currentNode = $currentNode->getCameFromNode();
}

$normalizedMap = $getNormalizedMap();
$paintMap();

// Find shortest path

$queue = new class extends SplPriorityQueue
{
    public function compare($priority1, $priority2)
    {
        return $priority1 <=> $priority2;
    }
};

/** @var Node $startNode */
$startNode = $map[0][0];

$queue->insert($startNode, $startNode->getDistance());

$visited = [];
$previous = [];
$dist = [];

while (!$queue->isEmpty()) {
    /** @var Node $currentNode */
    $currentNode = $queue->extract();

    if (isset($visited[spl_object_hash($currentNode)])) {
        continue;
    }

    $visited[spl_object_hash($currentNode)] = true;

    /** @var Node $neighbor */
    foreach ($currentNode->getNeighbors() as $direction => $_) {
        $neighborX = $currentNode->getX() + $directionCoefs[$direction][0];
        $neighborY = $currentNode->getY() + $directionCoefs[$direction][1];

        $neighbor = $normalizedMap[$neighborY][$neighborX] ?? null;

        if (is_null($neighbor) || $neighbor->getType() === TILE_WALL) {
            continue;
        }

        $alt = ($dist[spl_object_hash($currentNode)] ?? 0) + 1;

        if (!isset($dist[spl_object_hash($neighbor)]) || $alt < $dist[spl_object_hash($neighbor)]) {
            $previous[spl_object_hash($neighbor)] = $currentNode;

            $dist[spl_object_hash($neighbor)] = $alt;

            $queue->insert($neighbor, $alt);
        }
    }
}

$seeker = $target;

while ($seeker !== $startNode) {
    $seeker = $previous[spl_object_hash($seeker)];
    print($seeker.PHP_EOL);
}

print('Shortest distance: '.$dist[spl_object_hash($target)].PHP_EOL);

$endTime = microtime(true);
$execution_time = ($endTime - $startTime);
print("Execution time: {$execution_time} sec");
