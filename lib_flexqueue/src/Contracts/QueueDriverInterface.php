<?php

declare(strict_types=1);

namespace Mason\FlexQueue\Contracts;

interface QueueDriverInterface
{
    /**
     * @param BaseJob $job
     */
    public function push(BaseJob $job): void;

    /**
     * @return void
     */
    public function consume(): void;
}
