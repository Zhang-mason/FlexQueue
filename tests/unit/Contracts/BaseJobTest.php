<?php

declare(strict_types=1);

namespace Tests\Contracts;

use Mason\FlexQueue\Contracts\BaseJob;
use PHPUnit\Framework\TestCase;

final class BaseJobTest extends TestCase
{
    public function testDefaultQueueIsDefault(): void
    {
        $job = new FakeJob();

        self::assertSame('default', $job->getQueue());
    }

    public function testCanSetQueueAndId(): void
    {
        $job = new FakeJob();
        $job->setQueue('critical');
        $job->setId(99);

        self::assertSame('critical', $job->getQueue());
        self::assertSame(99, $job->getId());
    }

    public function testRunExecutesHooksAndHandleInOrder(): void
    {
        $job = new FakeJob();

        $job->run();

        self::assertSame(['before', 'handle', 'after'], $job->events);
    }

    public function testAfterHookStillRunsOnException(): void
    {
        $job = new ThrowingFakeJob();

        try {
            $job->run();
            self::fail('Exception expected');
        } catch (\RuntimeException $e) {
            self::assertSame('boom', $e->getMessage());
        }

        self::assertSame(['before', 'handle', 'after'], $job->events);
    }
}

class FakeJob extends BaseJob
{
    /** @var list<string> */
    public array $events = [];

    protected function beforeHandle(): void
    {
        $this->events[] = 'before';
    }

    public function handle(): void
    {
        $this->events[] = 'handle';
    }

    protected function afterHandle(): void
    {
        $this->events[] = 'after';
    }
}

final class ThrowingFakeJob extends FakeJob
{
    public function handle(): void
    {
        parent::handle();
        throw new \RuntimeException('boom');
    }
}
