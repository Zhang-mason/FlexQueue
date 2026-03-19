<?php

declare(strict_types=1);

namespace Tests\Support;

use Mason\FlexQueue\Contracts\BaseJob;
use Mason\FlexQueue\Contracts\QueueDriverInterface;
use Mason\FlexQueue\Support\QueueManager;
use PHPUnit\Framework\TestCase;

final class QueueManagerTest extends TestCase
{
    public function testDispatchCallsDriverPush(): void
    {
        $driver = new class () implements QueueDriverInterface {
            public int $pushCalled = 0;
            public ?BaseJob $lastPushedJob = null;

            public function push(BaseJob $job): void
            {
                $this->pushCalled++;
                $this->lastPushedJob = $job;
            }

            public function pop(): ?BaseJob
            {
                return null;
            }

            public function handleError(BaseJob $job, \Throwable $th): void
            {
            }
        };

        $manager = new QueueManager($driver);
        $job = new class () extends BaseJob {
            public function handle(): void
            {
            }
        };

        $manager->dispatch($job);

        self::assertSame(1, $driver->pushCalled);
        self::assertSame($job, $driver->lastPushedJob);
    }

    public function testConsumePopsJobAndRunsIt(): void
    {
        $job = new class () extends BaseJob {
            public int $handleCalled = 0;

            public function handle(): void
            {
                $this->handleCalled++;
            }
        };

        $driver = new class ($job) implements QueueDriverInterface {
            public int $popCalled = 0;
            public int $handleErrorCalled = 0;
            public BaseJob $nextJob;

            public function __construct(BaseJob $nextJob)
            {
                $this->nextJob = $nextJob;
            }

            public function push(BaseJob $job): void
            {
            }

            public function pop(): ?BaseJob
            {
                $this->popCalled++;

                return $this->nextJob;
            }

            public function handleError(BaseJob $job, \Throwable $th): void
            {
                $this->handleErrorCalled++;
            }
        };

        $manager = new QueueManager($driver);

        $manager->consume();

        self::assertSame(1, $driver->popCalled);
        self::assertSame(1, $job->handleCalled);
        self::assertSame(0, $driver->handleErrorCalled);
    }

    public function testConsumeReturnsWhenNoJob(): void
    {
        $driver = new class () implements QueueDriverInterface {
            public int $popCalled = 0;
            public int $handleErrorCalled = 0;

            public function push(BaseJob $job): void
            {
            }

            public function pop(): ?BaseJob
            {
                $this->popCalled++;

                return null;
            }

            public function handleError(BaseJob $job, \Throwable $th): void
            {
                $this->handleErrorCalled++;
            }
        };

        $manager = new QueueManager($driver);

        $manager->consume();

        self::assertSame(1, $driver->popCalled);
        self::assertSame(0, $driver->handleErrorCalled);
    }

    public function testConsumeCallsHandleErrorWhenJobThrows(): void
    {
        $job = new class () extends BaseJob {
            public int $handleCalled = 0;

            public function handle(): void
            {
                $this->handleCalled++;
                throw new \RuntimeException('job failed');
            }
        };

        $driver = new class ($job) implements QueueDriverInterface {
            public int $popCalled = 0;
            public int $handleErrorCalled = 0;
            public ?BaseJob $lastErroredJob = null;
            public ?\Throwable $lastError = null;
            public BaseJob $nextJob;

            public function __construct(BaseJob $nextJob)
            {
                $this->nextJob = $nextJob;
            }

            public function push(BaseJob $job): void
            {
            }

            public function pop(): ?BaseJob
            {
                $this->popCalled++;

                return $this->nextJob;
            }

            public function handleError(BaseJob $job, \Throwable $th): void
            {
                $this->handleErrorCalled++;
                $this->lastErroredJob = $job;
                $this->lastError = $th;
            }
        };

        $manager = new QueueManager($driver);

        $manager->consume();

        self::assertSame(1, $driver->popCalled);
        self::assertSame(1, $job->handleCalled);
        self::assertSame(1, $driver->handleErrorCalled);
        self::assertSame($job, $driver->lastErroredJob);
        self::assertInstanceOf(\RuntimeException::class, $driver->lastError);
    }
}
