<?php

declare(strict_types=1);

namespace Mason\FlexQueue\Support;

use Mason\FlexQueue\Contracts\JobInterface;
use Mason\FlexQueue\Contracts\QueueDriverInterface;

final class QueueManager
{
    public function __construct(private QueueDriverInterface $driver)
    {
    }

    /**
     * @param array<string,mixed> $payload
     * @param array<string,mixed> $options
     */
    public function dispatch(JobInterface $jobInterface): void
    {
        $this->driver->push($jobInterface);
    }
    /**
     * @return array<string,mixed>|null
     */
    public function consume(): void
    {
        try {
            $this->driver->consume();
        } catch (\Throwable $th) {
            $this->handleError($th);
        }
    }
    public function getDriver(): QueueDriverInterface
    {
        return $this->driver;
    }
    private function handleError(\Throwable $th): void
    {
        // Handle the error (e.g., log it)
    }
}


