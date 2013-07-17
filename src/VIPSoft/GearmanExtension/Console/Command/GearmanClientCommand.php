<?php
/**
 * @copyright 2012 Anthon Pang
 * @license MIT
 */

namespace VIPSoft\GearmanExtension\Console\Command;

use Behat\Behat\Console\Command\BehatCommand;

use Behat\Gherkin\Gherkin;

/**
 * Behat-Gearman client console command
 *
 * @author Anthon Pang <apang@softwaredevelopment.ca>
 */
class GearmanClientCommand extends BehatCommand
{
    /**
     * {@inheritdoc}
     */
    protected function runFeatures(Gherkin $gherkin)
    {
        $eventService  = $this->getContainer()->get('behat.gearman.service.event');
        $clientService = $this->getContainer()->get('behat.gearman.service.client');
        $clientService->setCallback(array($eventService, 'replay'));

        foreach ($this->getFeaturesPaths() as $path) {
            // parse every feature with Gherkin
            $features = $gherkin->load((string) $path);

            // and use a worker to run it in FeatureTester
            foreach ($features as $feature) {
                $task = array(
                    'path'    => $feature->getFile(),
                    'dryRun'  => $this->isDryRun(),
                    'strict'  => $this->isStrict(),
                    'filters' => $gherkin->getFilters(),
                );

                $work = $clientService->create();
                $work->setContent($task);

                $clientService->assemble($work, $feature->getTags());
            }
        }

        $clientService->produce();
    }
}
