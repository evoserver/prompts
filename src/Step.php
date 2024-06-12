<?php

namespace Laravel\Prompts;

use Closure;

class Step
{
    public function __construct(
        public Closure $step,
        protected Closure|false $revert,
        public ?string $key,
        protected ?self $previous,
    ) {
    }

    /**
     * Execute this step.
     *
     * @param  array<mixed>  $responses
     */
    public function run(array $responses): mixed
    {
        return ($this->step)($responses);
    }

    /**
     * Revert to the previous step.
     *
     * @param  array<mixed>  $responses
     */
    public function revert(mixed $responses): void
    {
        // if ($this->canRevert()) {
        if ($this->previous?->revert instanceof Closure) {
            ($this->previous->revert)($responses);
        }
    }

    /**
     * Whether the previous step allows reverting.
     */
    public function canRevert(): bool
    {
        return $this->previous?->revert instanceof Closure;
    }
}
