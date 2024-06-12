<?php

namespace Laravel\Prompts;

use Closure;

class StepBuilder
{
    protected array $steps = [];

    protected array $responses = [];

    public function add(Closure $step, Closure|false $revert = null, string $key = null)
    {

        if ($revert === null) {
            $revert = fn() => null;
        }

        $previousStep = $this->steps[count($this->steps) - 1] ?? null;
        $this->steps[] = new Step($step, $revert, $key, $previousStep);

        return $this;

    }

    public function run(): array
    {
        $index = 0;

        while ($index < count($this->steps)) {
            $step = $this->steps[$index];

            $wasReverted = false;

            $step->canRevert()
                ? Prompt::revertUsing(function () use (&$wasReverted) {
                $wasReverted = true;
            })
                : Prompt::preventReverting();

            $this->responses[$step->key ?? $index] = $step->run($this->responses);

            if (!$wasReverted) {
                $index++;
                continue;
            }

            $step->revert($this->responses);
            $index--;

        }

        Prompt::preventReverting();

        return $this->responses;
    }

}

