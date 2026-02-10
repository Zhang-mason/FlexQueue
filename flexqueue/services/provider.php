<?php

declare(strict_types=1);

use Joomla\CMS\Factory;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\CMS\Extension\PluginInterface;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Database\DatabaseInterface;
use Joomla\Event\DispatcherInterface;
use Mason\Plugin\System\FlexQueue\Extension\FlexQueue;

return new class() implements ServiceProviderInterface {
    public function register(Container $container): void
    {
        $container->set(
            PluginInterface::class,
            function (Container $container) {
                $dispatcher = $container->get(DispatcherInterface::class);
                $plugin = new FlexQueue(
                    $dispatcher,
                    (array) PluginHelper::getPlugin('system', 'flexqueue'),
                    Factory::getApplication()
                );
                $plugin->setDatabase($container->get(DatabaseInterface::class));
                return $plugin;
            }
        );
    }
};
