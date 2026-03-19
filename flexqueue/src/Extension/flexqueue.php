<?php

declare(strict_types=1);

namespace Mason\Plugin\System\FlexQueue\Extension;

use Joomla\Application\ApplicationEvents;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Factory;
use Joomla\Event\SubscriberInterface;
use Joomla\Database\DatabaseAwareTrait;
use Mason\FlexQueue\Contracts\HellowWorld;
use Mason\FlexQueue\Service\QueueProvider;
use Mason\Plugin\System\FlexQueue\Command\AddQueueJobCommand;

final class FlexQueue extends CMSPlugin implements SubscriberInterface
{
    use DatabaseAwareTrait;

    public static function getSubscribedEvents(): array
    {
        return [
            'onAfterInitialise' => 'setQueueDriver',
            ApplicationEvents::BEFORE_EXECUTE => 'registerCommands',
        ];
    }

    public function setQueueDriver()
    {
        $container = Factory::getContainer();
        $params = $this->params;
        $container->registerServiceProvider(new QueueProvider($params));
    }
    public function registerCommands()
    {
        $app = Factory::getApplication();
        if ($app->isClient('cli')) {
            $this->setQueueDriver();
            $AddQueueJobCommand = new AddQueueJobCommand();
            $AddQueueJobCommand->setContainer(Factory::getContainer());
            $app->addCommand($AddQueueJobCommand);
        }
    }
}
