<?php

declare(strict_types=1);

namespace Mason\FlexQueue\Support;

use Mason\FlexQueue\Contracts\BaseJob;
use Mason\FlexQueue\Contracts\QueueDriverInterface;

final class QueueManager
{
    public function __construct(private QueueDriverInterface $driver)
    {
    }

    /**
     * @param BaseJob $job
     */
    public function dispatch(BaseJob $job): void
    {
        $this->driver->push($job);
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
