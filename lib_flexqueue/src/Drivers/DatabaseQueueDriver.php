<?php

declare(strict_types=1);

namespace Mason\FlexQueue\Drivers;

use Joomla\Database\DatabaseAwareTrait;
use Mason\FlexQueue\Contracts\QueueDriverInterface;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;
use RuntimeException;
use DateTimeImmutable;
use DateTimeZone;

final class DatabaseQueueDriver implements QueueDriverInterface
{
    use DatabaseAwareTrait;

    /** @param array<string,mixed> $config */
    public function __construct(private array $config)
    {
        $this->setDatabase(Factory::getContainer()->get(DatabaseInterface::class));
    }

    public function push(string $jobClass, array $payload, array $options = []): void
    {
        $db = $this->getDatabase();
        $queue = $options['queue'] ?? $this->config['queue'] ?? 'default';
        $delay = (int) ($options['delay'] ?? 0);
        $createdAt = Factory::getDate('now', 'Asia/Taipei');
        $availableAt = (clone $createdAt)->modify(sprintf('+%d seconds', max(0, $delay)));

        $payloadData = [
            'job' => $jobClass,
            'data' => $payload,
            'options' => $options,
        ];

        $payloadJson = json_encode($payloadData);

        if ($payloadJson === false) {
            throw new RuntimeException('Failed to encode job payload as JSON.');
        }

        $query = $db->getQuery(true)
            ->insert($db->quoteName("#__flexqueue_jobs"))
            ->columns([
                $db->quoteName('queue'),
                $db->quoteName('payload'),
                $db->quoteName('available_at'),
                $db->quoteName('created_at'),
            ])
            ->values(
                implode(",", [
                    $db->quote((string) $queue),
                    $db->quote($payloadJson),
                    $db->quote($availableAt->format('Y-m-d H:i:s')),
                    $db->quote($createdAt->format('Y-m-d H:i:s')),
                ])
            );

        $db->setQuery($query);
        $db->execute();
    }

    public function consume(): ?array
    {
        $db = $this->getDatabase();
        $queue = $this->config['queue'] ?? 'default';
        $reserveTimeout = (int) ($this->config['reserve_timeout'] ?? 60);
        $workerId = $this->getWorkerId();
        $now = Factory::getDate('now', 'Asia/Taipei');
        $nowSql = $db->quote($now->format('Y-m-d H:i:s'));
        $reserveUntil = (clone $now)->modify(sprintf('+%d seconds', max(1, $reserveTimeout)));
        $reserveUntilSql = $db->quote($reserveUntil->format('Y-m-d H:i:s'));

        $db->transactionStart();

        try {
            $query = $db->getQuery(true)
                ->select('*')
                ->from($db->quoteName('#__flexqueue_jobs'))
                ->where($db->quoteName('queue') . ' = ' . $db->quote((string) $queue))
                ->where($db->quoteName('available_at') . ' <= ' . $nowSql)
                ->where('(' . $db->quoteName('reserved_until') . ' IS NULL OR ' . $db->quoteName('reserved_until') . ' <= ' . $nowSql . ')')
                ->order($db->quoteName('id') . ' ASC');

            $db->setQuery($query, 0, 1);
            $job = $db->loadAssoc();

            if ($job === null) {
                $db->transactionCommit();
                return null;
            }

            $update = $db->getQuery(true)
                ->update($db->quoteName('#__flexqueue_jobs'))
                ->set($db->quoteName('reserved_at') . ' = ' . $nowSql)
                ->set($db->quoteName('reserved_until') . ' = ' . $reserveUntilSql)
                ->set($db->quoteName('reserved_by') . ' = ' . $db->quote($workerId))
                ->set($db->quoteName('attempts') . ' = ' . $db->quoteName('attempts') . ' + 1')
                ->where($db->quoteName('id') . ' = ' . (int) $job['id'])
                ->where($db->quoteName('available_at') . ' <= ' . $nowSql)
                ->where('(' . $db->quoteName('reserved_until') . ' IS NULL OR ' . $db->quoteName('reserved_until') . ' <= ' . $nowSql . ')');

            $db->setQuery($update);
            $db->execute();

            if ($db->getAffectedRows() < 1) {
                $db->transactionCommit();
                return null;
            }

            $db->transactionCommit();
        } catch (\Throwable $th) {
            $db->transactionRollback();
            throw $th;
        }

        $decodedPayload = json_decode((string) ($job['payload'] ?? ''), true);

        if (!is_array($decodedPayload)) {
            $decodedPayload = ['raw' => $job['payload'] ?? null];
        }

        return [
            'id' => (int) ($job['id'] ?? 0),
            'queue' => (string) ($job['queue'] ?? ''),
            'attempts' => (int) ($job['attempts'] ?? 0),
            'reserved_at' => $job['reserved_at'] ?? null,
            'reserved_until' => $job['reserved_until'] ?? null,
            'payload' => $decodedPayload,
            'job' => $decodedPayload['job'] ?? null,
            'data' => $decodedPayload['data'] ?? null,
            'options' => $decodedPayload['options'] ?? null,
        ];
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
