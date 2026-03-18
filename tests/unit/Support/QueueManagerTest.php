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
        $driver = new SpyDriver();
        $manager = new QueueManager($driver);
        $job = new QueueManagerFakeJob();

        $manager->dispatch($job);

        self::assertSame(1, $driver->pushCalled);
        self::assertSame($job, $driver->lastPushedJob);
    }

    public function testConsumeCallsDriverConsume(): void
    {
        $driver = new SpyDriver();
        $manager = new QueueManager($driver);

        $manager->consume();

        self::assertSame(1, $driver->consumeCalled);
    }

    public function testConsumeSwallowsDriverException(): void
    {
        $driver = new SpyDriver();
        $driver->throwOnConsume = true;
        $manager = new QueueManager($driver);

        $manager->consume();

        self::assertSame(1, $driver->consumeCalled);
        self::assertTrue(true);
    }
}

final class SpyDriver implements QueueDriverInterface
{
    public int $pushCalled = 0;
    public int $consumeCalled = 0;
    public bool $throwOnConsume = false;
    public ?BaseJob $lastPushedJob = null;

    public function push(BaseJob $job): void
    {
        $this->pushCalled++;
        $this->lastPushedJob = $job;
    }

    public function consume(): void
    {
        $this->consumeCalled++;

        if ($this->throwOnConsume) {
            throw new \RuntimeException('consume failed');
        }
    }
}

final class QueueManagerFakeJob extends BaseJob
{
    public function handle(): void
    {
    }
}
