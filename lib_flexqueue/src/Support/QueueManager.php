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
        $job = $this->driver->pop();
        if ($job === null) {
            // No job available, handle accordingly (e.g., sleep or exit)
            return;
        }
        try {
            $job->run();
        } catch (\Throwable $th) {
            $this->driver->handleError($job, $th);
        }
    }
    public function getDriver(): QueueDriverInterface
    {
        return $this->driver;
    }
}
