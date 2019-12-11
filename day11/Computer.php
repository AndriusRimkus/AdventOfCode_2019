<?php declare(strict_types=1);

class Computer
{
    private $memory = [];
    private $input = [];
    private $pointer = 0;
    private $relativeBase = 0;

    public function __construct(array $memory, array $initialInput)
    {
        $this->memory = $memory;
        $this->input = $initialInput;
    }

    public function getComputerOutput(): Generator
    {
        while (true) {
            $code = $this->memory[$this->pointer];

            $opCode = (int)($code % 100);

            if ($opCode === 99) {
                throw new Exception('Halt');
            }

            [$param1, $param2, $param3] = $this->getParamValues($code);
            [$param1Pointer, $param2Pointer, $param3Pointer] = $this->getParamPointers($code);

            if (in_array($opCode, [1, 2, 7, 8])) {
                $storage = &$this->memory[$param3Pointer];
            }

            if ($opCode === 1) {
                $storage = $param1 + $param2;
                $this->pointer += 4;
            }

            if ($opCode === 2) {
                $storage = $param1 * $param2;
                $this->pointer += 4;
            }

            if ($opCode === 3) {
                $input = array_shift($this->input);

                if (is_null($input)) {
                    throw new Exception('No input');
                }

                $this->memory[$param1Pointer] = $input;
                $this->pointer += 2;
            }

            if ($opCode === 4) {
                yield $param1;
                $this->pointer += 2;
            }

            if ($opCode === 5) {
                $this->pointer = $param1 !== 0 ? $param2 : $this->pointer + 3;
            }

            if ($opCode === 6) {
                $this->pointer = $param1 === 0 ? $param2 : $this->pointer + 3;
            }

            if ($opCode === 7) {
                $storage = $param1 < $param2 ? 1 : 0;
                $this->pointer += 4;
            }

            if ($opCode === 8) {
                $storage = $param1 === $param2 ? 1 : 0;
                $this->pointer += 4;
            }

            if ($opCode === 9) {
                $this->relativeBase += $param1;
                $this->pointer += 2;
            }
        }
    }

    private function getParamValues(int $code): array
    {
        [$param1Pointer, $param2Pointer, $param3Pointer] = $this->getParamPointers($code);

        return [
            $this->memory[$param1Pointer] ?? 0,
            $this->memory[$param2Pointer] ?? 0,
            $this->memory[$param3Pointer] ?? 0
        ];
    }

    private function getParamPointers(int $code): array
    {
        $paramModes = (int)($code / 100);

        // 0 - position mode
        // 1 - immediate
        // 2 - relative

        $param1Mode = $paramModes % 10;
        $param2Mode = (int)($paramModes % 100 / 10);
        $param3Mode = (int)($paramModes / 100);

        $param1 = $this->pointer + 1;
        $param2 = $this->pointer + 2;
        $param3 = $this->pointer + 3;

        if ($param1Mode === 0) {
            $param1 = $this->memory[$param1] ?? 0;
        }

        if ($param2Mode === 0) {
            $param2 = $this->memory[$param2] ?? 0;
        }

        if ($param3Mode === 0) {
            $param3 = $this->memory[$param3] ?? 0;
        }

        if ($param1Mode === 2) {
            $param1 = $this->relativeBase + $this->memory[$param1] ?? 0;
        }

        if ($param2Mode === 2) {
            $param2 = $this->relativeBase + $this->memory[$param2] ?? 0;
        }

        if ($param3Mode === 2) {
            $param3 = $this->relativeBase + $this->memory[$param3] ?? 0;
        }

        return [$param1, $param2, $param3];
    }

    public function loadInput(array $input)
    {
        $this->input = array_merge($this->input, $input);
    }

    public function getMemory(): array
    {
        return $this->memory;
    }
}
