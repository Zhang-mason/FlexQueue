#!/usr/bin/php
<?php

// We need to classify this script as Joomla entry point
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

    public function __construct()
    {
        // Load the configuration
        $config = new \Joomla\Registry\Registry(new \JConfig());

        // We set the `pid file` manually but should be done in the configuration.php 
        $config->set('application_pid_file', '/run/QueueDaemon.pid');

        parent::__construct(null, $config);
        $driverConfig = $this->input->get('driver', 'database');
        if (!in_array($driverConfig, ['redis', 'rabbitmq', 'database'])) {
            $this->out('Driver is invalid: ' . $driverConfig);
            $this->stop();
        }
        $config = new \Joomla\Registry\Registry(
            [
                'flexqueue' => [
                    'driver' => $driverConfig,
                ],
            ]
        );
        $this->getContainer()->registerServiceProvider(
            new Mason\FlexQueue\Service\QueueProvider($config)
        );
    }

    // This function needs to be implemented since it's required by the CMSApplicationInterface  
    public function getName()
    {
        return $this->name;
    }

    // This function holds our business logic
    public function doExecute()
    {
        $this->out('Executing QueueDaemon...');

        /**
         * @var \Mason\FlexQueue\Support\QueueManager
         */
        $QueueManager = $this->getContainer()->get(
            \Mason\FlexQueue\Support\QueueManager::class
        );
        try {
            $QueueManager->consume();
        } catch (\Throwable $th) {
            $this->logger->error('Error during consuming queue: ' . $th->getMessage());
        }
    }
}

// Run the application
\Joomla\CMS\Application\DaemonApplication::getInstance('QueueDaemon')->execute();
