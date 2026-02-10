<?php

declare(strict_types=1);

namespace Mason\FlexQueue\Drivers;

use Mason\FlexQueue\Contracts\QueueDriverInterface;

final class RedisQueueDriver implements QueueDriverInterface
{
    /** @param array<string,mixed> $config */
    public function __construct(private array $config)
    {
    }

    public function push(string $jobClass, array $payload, array $options = []): void
    {
        // @todo implement redis push logic
    }

    public function consume(): ?array
    {
        // @todo implement redis pop logic
        return null;
    }
}


