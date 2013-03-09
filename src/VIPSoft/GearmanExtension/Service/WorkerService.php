<?php
/**
 * @copyright 2012 Anthon Pang
 * @license MIT
 */

namespace VIPSoft\GearmanExtension\Service;

use VIPSoft\GearmanExtension\Model\Payload;

/**
 * Gearman worker service
 *
 * @author Anthon Pang <apang@softwaredevelopment.ca>
 */
class WorkerService
{
    /**
     * Gearman server
     *
     * @var string
     */
    private $server;

    /**
     * Gearman task name (default)
     *
     * @var string
     */
    private $taskName;

    /**
     * Allowed custom task names (overrides default)
     *
     * @var array
     */
    private $customTaskNames;

    /**
     * Access token
     *
     * @var string
     */
    private $accessToken;

    /**
     * Encoder-decoder
     *
     * @var CodecService
     */
    private $codecService;

    /**
     * Gearman worker
     *
     * @var \GearmanWorker
     */
    private $worker;

    /**
     * Task completion callback
     *
     * @var mixed
     */
    private $callback;

    /**
     * Constructor
     *
     * @param string       $server          Gearman server
     * @param string       $taskName        Task name
     * @param array        $customTaskNames Custom task names
     * @param string       $accessToken     Access token
     * @param CodecService $codecService    Encode/decode service
     */
    public function __construct($server, $taskName, $customTaskNames, $accessToken, $codecService)
    {
        $this->server          = $server;
        $this->taskName        = $taskName;
        $this->customTaskNames = $customTaskNames;
        $this->accessToken     = $accessToken;
        $this->codecService    = $codecService;

        if (!$this->taskName && !$this->customTaskNames) {
            throw new \Exception('Please specify "task_name" or at least one "custom_task_name" in order to run gearman worker ("~" is null, not a "default value".)');
        }

        $this->createWorker();
    }

    /**
     * Set completion callback
     *
     * @param mixed $callback
     */
    public function setCallback($callback)
    {
        $this->callback = $callback;
    }

    /**
     * Consume work
     */
    public function consume()
    {
        while ($this->worker->work()) {
            ;
        }
    }

    /**
     * Handle new job
     *
     * @param \GearmanJob $job
     *
     * @return void|string Output
     *
     * {@internal Callback used internally by the service. }}
     */
    public function completeJob(\GearmanJob $job)
    {
        /**
         * @var Payload
         */
        $work = $this->codecService->decode($job->workload());

        if (! $work instanceof Payload
            || (isset($this->accessToken) && $work->getAccessToken() !== $this->accessToken)
        ) {
            return;
        }

        $content = is_callable($this->callback) ? call_user_func($this->callback, $work->getContent()) : null;

        $result = new Payload();
        $result->setAccessToken($this->accessToken);
        $result->setContent($content);

        return $this->codecService->encode($result);
    }

    /**
     * Register callback function(s) for a worker
     *
     * @param \GearmanWorker $worker
     */
    private function register(\GearmanWorker $worker)
    {
        $taskNames = array_merge(array($this->taskName), (array) $this->customTaskNames);

        foreach ($taskNames as $taskName) {
            $worker->addFunction($taskName, array($this, 'completeJob'));
        }
    }

    /**
     * Create Gearman worker
     *
     * @return void
     */
    private function createWorker()
    {
        $this->worker = new \GearmanWorker();

        $this->register($this->worker);

        if (isset($this->server)) {
            $this->worker->addServers($this->server);

            return;
        }

        $this->worker->addServer();
    }
}
