<?php

declare(strict_types=1);

namespace Tests\Support;

use InvalidArgumentException;
use Joomla\Registry\Registry;
use Mason\FlexQueue\Support\QueueFactory;
use PHPUnit\Framework\TestCase;

final class QueueFactoryTest extends TestCase
{
    public function testUnsupportedDriverThrowsException(): void
    {
        $factory = new QueueFactory();
        $config = new Registry(['driver' => 'unknown']);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported queue driver "unknown"');

        $factory->getDriver('unknown', $config);
    }
}
