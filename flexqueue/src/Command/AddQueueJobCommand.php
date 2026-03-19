<?php

declare(strict_types=1);

namespace Mason\Plugin\System\FlexQueue\Command;

defined('_JEXEC') or die;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Joomla\Console\Command\AbstractCommand;
use Joomla\DI\ContainerAwareInterface;
use Joomla\DI\ContainerAwareTrait;
use Mason\FlexQueue\Contracts\BaseJob;
use Mason\FlexQueue\Support\QueueManager;

class AddQueueJobCommand extends AbstractCommand implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * The default command name
     *
     * @var    string
     * @since  4.0.0
     */
    protected static $defaultName = 'flexqueue:add';
    /**
     * Stores the Input Object
     * @var InputInterface
     * @since 4.0.0
     */
    private $cliInput;

    /**
     * SymfonyStyle Object
     * @var SymfonyStyle
     * @since 4.0.0
     */
    private $ioStyle;

    /**
     * QueueManager instance
     * @var QueueManager
     * @since 4.0.0
     */
    private QueueManager $queueManager;
    /**
     * Configures the IO
     *
     * @param   InputInterface   $input   Console Input
     * @param   OutputInterface  $output  Console Output
     *
     * @return void
     *
     * @since 4.0.0
     *
     */
    private function configureIO(InputInterface $input, OutputInterface $output): void
    {
        $this->cliInput = $input;
        $this->ioStyle = new SymfonyStyle($input, $output);
    }

    protected function configure(): void
    {
        $this->setDescription('Add a job to the flex queue');

        $this->addOption(
            'job',
            null,
            InputOption::VALUE_REQUIRED,
            'Job class name (e.g. SendEmail)'
        );

        $this->addOption(
            'payload',
            null,
            InputOption::VALUE_OPTIONAL,
            'JSON encoded payload',
        );
    }

    protected function doExecute(InputInterface $input, OutputInterface $output): int
    {
        $this->configureIO($input, $output);
        $this->queueManager = $this->getContainer()->get(
            QueueManager::class
        );

        $job = $this->cliInput->getOption('job');
        $payload = $this->cliInput->getOption('payload');

        // 驗證必填
        if (empty($job) || !is_subclass_of($job, BaseJob::class) || !class_exists($job)) {
            $this->ioStyle->error('--job is required and must be a valid class name');
            return 1;
        }
        /** @var BaseJob $jobInstance */
        if (!empty($payload)) {
            if (!method_exists($job, 'fromArray')) {
                $this->ioStyle->error('--payload is not supported for this job');
                return 1;
            }
            $data = json_decode($payload, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->ioStyle->error('--payload must be valid JSON');
                return 1;
            }
            $jobInstance = $job::fromArray($data);
        } else {
            $jobInstance = new $job();
        }
        try {
            $this->queueManager->dispatch($jobInstance);
            $this->ioStyle->success(sprintf('Job [%s] added to queue', $job));
        } catch (\Exception $e) {
            $this->ioStyle->error('Failed to add job: ' . $e->getMessage());
            return 1;
        }
        return 0;
    }
}
