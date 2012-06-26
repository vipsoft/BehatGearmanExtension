<?php
/**
 * @copyright 2012 Anthon Pang
 * @license MIT
 */

namespace VIPSoft\GearmanExtension\Service;

use VIPSoft\GearmanExtension\Model\Payload;

/**
 * Gearman client service
 *
 * @author Anthon Pang <apang@softwaredevelopment.ca>
 */
class ClientService
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
     * Task completion callback
     *
     * @var mixed
     */
    private $callback;

    /**
     * Gearman client
     *
     * @var \GearmanClient
     */
    private $client;

    /**
     * Task ID
     *
     * @var integer
     */
    private $taskId;

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
        $this->taskId          = 1;

        $this->createClient();
    }

    /**
     * Set completion callback
     *
     * @param mixed $callback
     *
     * @return void
     */
    public function setCallback($callback)
    {
        $this->callback = $callback;
    }

    /**
     * Create Payload object
     *
     * @return Payload
     */
    public function create()
    {
        $payload = new Payload();
        $payload->setAccessToken($this->accessToken);

        return $payload;
    }

    /**
     * Queue up
     *
     * @param Payload $work      Work request
     * @param array   $taskNames Custom task names
     *
     * @return void
     */
    public function assemble(Payload $work, $taskNames = null)
    {
        $workload = $this->codecService->encode($work);

        $taskNames = array_intersect((array) $taskNames, (array) $this->customTaskNames);

        if (count($taskNames) === 0) {
            $taskNames = array($this->taskName);
        }

        foreach ($taskNames as $taskName) {
            $this->client->addTask($taskName, $workload, null, $this->taskId++);
        }
    }

    /**
     * Execute
     *
     * @return void
     */
    public function produce()
    {
        $this->client->runTasks();
    }

    /**
     * Task completion callback
     *
     * @param \GearmanTask $task
     *
     * @return void
     *
     * {@internal Callback used internally by the service. }}
     */
    public function taskCompleted(\GearmanTask $task)
    {
        /**
         * @var Payload
         */
        $result = $this->codecService->decode($task->data());

        if (!($result instanceof Payload)
            || (isset($this->accessToken) && $result->getAccessToken() !== $this->accessToken)
        ) {
            return;
        }

        if (is_callable($this->callback)) {
            call_user_func($this->callback, $result->getContent());
        }
    }

    /**
     * Create Gearman Client
     *
     * @return void
     */
    private function createClient()
    {
        $this->client = new \GearmanClient();
        $this->client->setCompleteCallback(array($this, 'taskCompleted'));

        if (isset($this->server)) {
            $this->client->addServers($this->server);

            return;
        }

        $this->client->addServer();
    }
}
