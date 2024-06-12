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

    public function run(array $responses): mixed
    {
        return ($this->step)($responses);
    }

    public function revert(mixed $responses): void
    {
        if ($this->canRevert()) {
            ($this->previous->revert)($responses);
        }
    }

    public function canRevert(): bool
    {
        return $this->previous?->revert !== false;
    }
}
