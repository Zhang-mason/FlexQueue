<?php

declare(strict_types=1);

namespace Mason\FlexQueue\Contracts;

interface QueueDriverInterface
{
    /**
     * @param BaseJob $job
     */
    public function push(BaseJob $job): void;
    public function pop(): ?BaseJob;
    public function handleError(BaseJob $job, \Throwable $th): void;
}
