<?php

declare(strict_types=1);

namespace Mason\FlexQueue\Drivers;

use Joomla\Database\DatabaseAwareTrait;
use Mason\FlexQueue\Contracts\QueueDriverInterface;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;
use Mason\FlexQueue\Contracts\BaseJob;
use Mason\FlexQueue\Contracts\JobInterface;
use stdClass;

final class DatabaseQueueDriver implements QueueDriverInterface
{
    use DatabaseAwareTrait;

    /** @param array<string,mixed> $config */
    public function __construct(private array $config)
    {
        $this->setDatabase(Factory::getContainer()->get(DatabaseInterface::class));
    }

    public function push(BaseJob $job): void
    {
        $db = $this->getDatabase();
        $createdAt = Factory::getDate('now', 'Asia/Taipei');
        $message = new stdClass();
        $message->payload = serialize($job);
        $message->queue = $job->getQueue();
        $message->worker_id = $this->getWorkerId();
        $message->created_at = $createdAt->toSql(true);
        $message->available_at = $createdAt->toSql(true);
        $db->insertObject('#__flexqueue_jobs', $message);
    }

    public function consume(): ?array
    {
    }
    private function getWorkerId(): string
    {
        if (!empty($this->config['worker_id'])) {
            return (string) $this->config['worker_id'];
        }
        $host = gethostname();
        $pid = getmypid();
        return sprintf('%s:%s', $host ?: 'worker', $pid ?: '0');
    }
}
