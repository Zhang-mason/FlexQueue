<?php

use Joomla\CMS\Application\AdministratorApplication;
use Joomla\CMS\Installer\InstallerAdapter;
use Joomla\CMS\Installer\InstallerScriptInterface;
use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseInterface;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Filesystem\File;
use Joomla\Filesystem\Exception\FilesystemException;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

return new class () implements ServiceProviderInterface {
    public function register(Container $container)
    {
        $container->set(
            InstallerScriptInterface::class,
            new class ($container->get(AdministratorApplication::class), $container->get(DatabaseInterface::class)) implements InstallerScriptInterface {
            private AdministratorApplication $app;
            private DatabaseInterface $db;

            public function __construct(AdministratorApplication $app, DatabaseInterface $db)
            {
                $this->app = $app;
                $this->db = $db;
            }

            public function install(InstallerAdapter $parent): bool
            {
                $this->app->enqueueMessage('Successful installed.');
                $src = JPATH_LIBRARIES . '/lib_flexqueue/src/Cli/QueueDaemon.php';
                $dest = JPATH_CLI . '/QueueDaemon.php';
                if (file_exists($src) && !file_exists($dest)) {
                    try {
                        \Joomla\Filesystem\File::move($src, $dest);
                    } catch (\Exception $e) {
                        $this->app->enqueueMessage('Move failed: ' . $e->getMessage(), 'error');
                    }
                }

                return true;
            }

            public function update(InstallerAdapter $parent): bool
            {
                $this->app->enqueueMessage('Successful updated.');

                return true;
            }

            public function uninstall(InstallerAdapter $parent): bool
            {
                $this->app->enqueueMessage('Successful uninstalled.');
                try {
                    $this->db->dropTable('#__flexqueue_jobs');
                    $this->db->dropTable('#__flexqueue_job_errors');
                } catch (\Exception $e) {
                    $this->app->enqueueMessage('DB cleanup failed: ' . $e->getMessage(), 'error');
                }
                $file = JPATH_CLI . '/QueueDaemon.php';
                if (file_exists($file)) {
                    try {
                        \Joomla\Filesystem\File::delete($file);
                    } catch (\Exception $e) {
                        $this->app->enqueueMessage('Delete failed: ' . $e->getMessage(), 'error');
                    }
                }

                return true;
            }

            public function preflight(string $type, InstallerAdapter $parent): bool
            {
                return true;
            }

            public function postflight(string $type, InstallerAdapter $parent): bool
            {
                return true;
            }
            }
        );
    }
};