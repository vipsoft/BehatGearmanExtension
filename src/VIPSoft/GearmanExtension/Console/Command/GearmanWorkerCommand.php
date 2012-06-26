<?php
/**
 * @copyright 2012 Anthon Pang
 * @license MIT
 */

namespace VIPSoft\GearmanExtension\Console\Command;

use Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Output\OutputInterface;

use Behat\Behat\Console\Command\BehatCommand;

use Behat\Gherkin\Gherkin;

/**
 * Behat-Gearman worker console command
 *
 * @author Anthon Pang <apang@softwaredevelopment.ca>
 */
class GearmanWorkerCommand extends BehatCommand
{
    /**
     * Callback to process new work
     *
     * @param array $task
     *
     * @return array
     */
    public function runFeature($task)
    {
        $eventService = $this->getContainer()->get('behat.gearman.service.event');
        $eventService->flushEvents();

        $path = trim(strip_tags($task['path']));

        if (substr($path, -8) === '.feature' && is_file($path)) {
            $this->setFeaturesPaths(array($path));
            $this->setDryRun((boolean) $task['dryRun']);
            $this->setStrict((boolean) $task['strict']);

            $this->beforeSuite();

            parent::runFeatures($this->getContainer()->get('gherkin'));

            $this->afterSuite();
        }

        return $eventService->getEvents();
    }

    /**
     * {@inheritdoc}
     */
    protected function runFeatures(Gherkin $gherkin)
    {
        $workerService = $this->getContainer()->get('behat.gearman.service.worker');
        $workerService->setCallback(array($this, 'runFeature'));
        $workerService->consume();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->runFeatures($this->getContainer()->get('gherkin'));

        return $this->getCliReturnCode();
    }
}
