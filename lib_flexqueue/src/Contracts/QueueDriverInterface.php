<?php

declare(strict_types=1);

namespace Mason\FlexQueue\Contracts;

interface QueueDriverInterface
{
    /**
     * @param array<string,mixed> $payload
     * @param array<string,mixed> $options
     */
    public function push(string $jobClass, array $payload, array $options = []): void;

    /**
     * @return array<string,mixed>|null
     */
    public function consume(): ?array;
}


