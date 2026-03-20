<?php

declare(strict_types=1);

namespace Mason\FlexQueue\Drivers;

use Joomla\Database\DatabaseAwareTrait;
use Mason\FlexQueue\Contracts\QueueDriverInterface;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;
use Mason\FlexQueue\Contracts\BaseJob;
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

    public function pop(): ?BaseJob
    {
        $db = $this->getDatabase();
        $now = Factory::getDate('now', 'Asia/Taipei');
        $available_at = $now->toSql(true);
        $job = null;
        $jobData = null;
        $queue = 'default';
        $db->transactionStart();
        try {
            $db->lockTable("#__flexqueue_jobs");
            $query = $db->getQuery(true)
                ->select('*')
                ->from($db->quoteName('#__flexqueue_jobs'))
                ->where("queue = :queue")
                ->where('available_at <= :available_at')
                ->bind(':queue', $queue, ParameterType::STRING)
                ->bind(':available_at', $available_at, ParameterType::STRING)
                ->order($db->quoteName('created_at') . ' ASC');
            $jobData = $db->setQuery($query)->loadObject();
            if (!$jobData) {
                $db->transactionCommit();
                return null;
            }
            $job = unserialize((string) $jobData->payload);
            if (!$job instanceof BaseJob) {
                throw new \RuntimeException('Invalid job payload');
            }
            $this->deleteJob((int) $jobData->id);
            $db->transactionCommit();
            return $job;
        } catch (\Throwable $e) {
            $db->transactionRollback();
            throw $e;
        } finally {
            $db->unlockTables();
        }
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
    public function handleError(BaseJob $job, \Throwable $e): void
    {
        $error = new stdClass();
        $error->error_message = $e->__toString();
        $error->job_id = $job->getId();
        $error->queue = $job->getQueue();
        $error->payload = serialize($job);
        $error->error_at = Factory::getDate('now', 'Asia/Taipei')->toSql(true);
        $db = $this->getDatabase();
        $db->insertObject('#__flexqueue_job_errors', $error);
    }
    private function deleteJob(int $jobId): void
    {
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->delete($db->quoteName('#__flexqueue_jobs'))
            ->where('id = :id')
            ->bind(':id', $jobId, ParameterType::INTEGER);
        $db->setQuery($query)->execute();
    }
}
