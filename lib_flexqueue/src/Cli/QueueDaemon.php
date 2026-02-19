#!/usr/bin/php
<?php

// We need to classify this script as Joomla entry point

use Joomla\CMS\Extension\ExtensionHelper;
use Joomla\Registry\Registry;

define('_JEXEC', 1);

// Set the Joomla basepath one folder up relative to our current directory /cli
define('JPATH_BASE', dirname(__DIR__));

// Load the definitions and the framework
require_once JPATH_BASE . '/includes/defines.php';
require_once JPATH_BASE . '/includes/framework.php';
require_once JPATH_LIBRARIES . '/lib_flexqueue/autoload.php';

// Add a simple 'echo' logger
\Joomla\CMS\Log\Log::addLogger(['logger' => 'Echo']);

/**
 * The application which handles our business logic 
 */
class QueueDaemon extends \Joomla\CMS\Application\DaemonApplication
{
    // Set the name of the application
    public $name = 'QueueDaemon';
    /**
     * @var \Mason\FlexQueue\Support\QueueManager
     */
    public $queueManager;

    public function __construct()
    {
        // Load the configuration
        $config = new Registry(new \JConfig());

        // We set the `pid file` manually but should be done in the configuration.php 
        $config->set('application_pid_file', '/run/QueueDaemon.pid');

        parent::__construct(null, $config);
    }

    // This function needs to be implemented since it's required by the CMSApplicationInterface  
    public function getName()
    {
        return $this->name;
    }

    // This function holds our business logic
    public function doExecute()
    {
        try {
            if (!$this->queueManager) {
                $this->initializeQueueManager();
            }
            $this->queueManager->consume();
        } catch (\Throwable $th) {
            $this->out('Error during consuming queue: ' . $th->getMessage());
            $this->stop();
        }
    }
    private function initializeQueueManager(): void
    {
        $plugin = ExtensionHelper::getExtensionRecord('flexqueue', 'plugin', null, 'system');

        if (!$plugin || !$plugin->enabled) {
            throw new \RuntimeException('FlexQueue plugin not found or disabled');
        }

        $params = new Registry($plugin->params);

        $this->getContainer()->registerServiceProvider(
            new Mason\FlexQueue\Service\QueueProvider($params)
        );

        $this->queueManager = $this->getContainer()->get(
            \Mason\FlexQueue\Support\QueueManager::class
        );
    }
}
// Run the application
\Joomla\CMS\Application\DaemonApplication::getInstance('QueueDaemon')->execute();
