<?php

declare(strict_types=1);

namespace Mason\FlexQueue\Drivers;

use Mason\FlexQueue\Contracts\BaseJob;
use Mason\FlexQueue\Contracts\QueueDriverInterface;
use Redis;

final class RedisQueueDriver implements QueueDriverInterface
{
    /** @var ?\Redis */
    private ?\Redis $_redis = null;

    /** @param array<string,mixed> $config */
    public function __construct(private array $config)
    {
        if (!self::isSupported()) {
            throw new \RuntimeException('Redis extension is not installed');
        }
        if (!$this->isConnected()) {
            $this->_redis = $this->createConnection();
        }
    }

    public function push(BaseJob $job): void
    {
        $data = serialize($job);
        $queueKey = $this->getQueueKey($job->getQueue());
        $this->_redis->rPush($queueKey, $data);
    }

    public function consume(): void
    {
        $queue = 'default';
        $queueKey = $this->getQueueKey($queue);

        // Use blPop to wait up to 60 seconds.
        $result = $this->_redis->blPop($queueKey, 60);

        if (!$result || !isset($result[1])) {
            return;
        }

        $payload = $result[1];

        try {
            $job = unserialize($payload);

            if (!$job instanceof BaseJob) {
                throw new \RuntimeException('Invalid job payload');
            }

            // Run the job.
            $job->run();
        } catch (\Throwable $e) {
            // Record the error in Redis.
            $this->handleException($e, $payload, $queue);
        }
    }

    /**
     * Test to see if the storage handler is available.
     *
     * @return  boolean
     */
    public static function isSupported()
    {
        return class_exists('\\Redis');
    }

    /**
     * Test to see if the Redis connection is available.
     *
     * @return  boolean
     */
    public function isConnected()
    {
        return isset($this->_redis) && $this->_redis instanceof \Redis;
    }

    private function createConnection(): \Redis
    {
        $redis = new \Redis();

        $host = $this->config['redis_host'] ?? 'localhost';
        $port = (int) ($this->config['redis_port'] ?? 6379);
        $password = $this->config['redis_password'] ?? null;
        $database = (int) ($this->config['redis_database'] ?? 0);
        try {
            if (!$redis->connect($host, $port)) {
                throw new \RuntimeException('Unable to connect to Redis');
            }
            if ($password) {
                $redis->auth($password);
            }
            $redis->select($database);
        } catch (\Throwable $e) {
            throw new \RuntimeException('Redis connection failed: ' . $e->getMessage());
        }
        return $redis;
    }

    /**
     * Get the Redis key for a queue.
     */
    private function getQueueKey(string $queue): string
    {
        return 'flexqueue:' . $queue;
    }

    /**
     * Handle a job failure.
     */
    private function handleException(\Throwable $e, string $payload, string $queue): void
    {
        $errorKey = 'flexqueue:errors:' . $queue;
        $errorData = [
            'error_message' => $e->__toString(),
            'queue' => $queue,
            'payload' => $payload,
            'error_at' => date('Y-m-d H:i:s'),
        ];
        $this->_redis->rPush($errorKey, serialize($errorData));
    }
}
