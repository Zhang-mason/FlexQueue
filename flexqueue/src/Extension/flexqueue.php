<?php

declare(strict_types=1);

namespace Mason\Plugin\System\Flexqueue\Extension;

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Factory;
use Joomla\Event\SubscriberInterface;
use Joomla\Database\DatabaseAwareTrait;
use Mason\FlexQueue\Contracts\HellowWorld;
use Mason\FlexQueue\Service\QueueProvider;

final class FlexQueue extends CMSPlugin implements SubscriberInterface
{
    use DatabaseAwareTrait;

    public static function getSubscribedEvents(): array
    {
        return [
            'onAfterInitialise' => 'setQueueDriver',
            'onAjaxFlexQueue' => 'handleAjaxRequest',
        ];
    }

    public function setQueueDriver()
    {
        $container = Factory::getContainer();
        $params = $this->params;
        $container->registerServiceProvider(new QueueProvider($params));
    }
    public function handleAjaxRequest()
    {
        /**
         * @var \Mason\FlexQueue\Support\QueueManager $QueueManager
         */
        $QueueManager = Factory::getContainer()->get(
            \Mason\FlexQueue\Support\QueueManager::class
        );
        $hellow = new HellowWorld();
        $QueueManager->dispatch($hellow);
    }
}
