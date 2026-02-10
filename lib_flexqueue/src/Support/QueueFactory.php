<?php

declare(strict_types=1);

namespace Mason\FlexQueue\Support;

use Mason\FlexQueue\Contracts\QueueDriverInterface;
use Mason\FlexQueue\Drivers\DatabaseQueueDriver;
use Mason\FlexQueue\Drivers\RedisQueueDriver;
use Joomla\Registry\Registry;
use InvalidArgumentException;

final class QueueFactory
{
    /**
     * @param Registry $config
     */
    public function getDriver(string $driverName, Registry $config): QueueDriverInterface
    {
        return match ($driverName) {
            'redis' => new RedisQueueDriver($config->get('flexqueue.redis', [])),
            'database' => new DatabaseQueueDriver($config->get('flexqueue.database', [])),
            default => throw new InvalidArgumentException(sprintf('Unsupported queue driver "%s"', $driverName)),
        };
    }
}

