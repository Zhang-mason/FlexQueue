<?php

declare(strict_types=1);

namespace Mason\FlexQueue\Contracts;

interface QueueDriverInterface
{
    /**
     * @param JobInterface $jobInterface
     */
    public function push(JobInterface $jobInterface): void;

    /**
     * @return array<string,mixed>|null
     */
    public function consume(): ?array;
}


