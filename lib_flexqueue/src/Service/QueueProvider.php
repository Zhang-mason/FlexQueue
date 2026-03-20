<?php
/**
 * Part of the Joomla Framework Database Package
 *
 * @copyright  Copyright (C) 2005 - 2021 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Mason\FlexQueue\Service;

use Mason\FlexQueue\Contracts\QueueDriverInterface;
use Mason\FlexQueue\Support\QueueFactory;
use Mason\FlexQueue\Support\QueueManager;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Registry\Registry;

/**
 * Database service provider
 *
 * @since  2.0.0
 */
class QueueProvider implements ServiceProviderInterface
{
    /** @param Registry $params */
    public function __construct(private Registry $params)
    {
    }

    /**
     * Registers the service provider with a DI container.
     *
     * @param   Container  $container  The DI container.
     *
     * @return  void
     *
     * @since   2.0.0
     */
    public function register(Container $container)
    {
        $container->set(QueueFactory::class, new QueueFactory());

        $container->share(
            QueueDriverInterface::class,
            function (Container $container): QueueDriverInterface {
                $factory = $container->get(QueueFactory::class);
                $driver = $this->params->get('driver', 'database');

                return $factory->getDriver($driver, $this->params);
            }
        );

        $container->share(
            QueueManager::class,
            fn (Container $container) => new QueueManager(
                $container->get(QueueDriverInterface::class)
            )
        );
    }
}
