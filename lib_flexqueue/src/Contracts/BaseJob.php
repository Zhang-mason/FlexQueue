<?php

namespace Mason\FlexQueue\Contracts;

abstract class BaseJob implements JobInterface
{
    private string $queue = 'default';
    private int $id = 0;

    public function __construct(?string $queue = null)
    {
        if ($queue !== null) {
            $this->queue = $queue;
        }
    }

    public function setQueue(string $queue): void
    {
        $this->queue = $queue;
    }

    public function getQueue(): string
    {
        return $this->queue;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id)
    {
        $this->id = $id;
    }

    // Wraps lifecycle hooks around the job execution.
    final public function run(): void
    {
        $this->beforeHandle();
        try {
            $this->handle();
        } finally {
            $this->afterHandle();
        }
    }

    protected function beforeHandle(): void
    {
    }

    protected function afterHandle(): void
    {
    }
}
