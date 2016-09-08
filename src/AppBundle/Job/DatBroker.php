<?php

namespace AppBundle\Job;

use Predis\Client;
use AppBundle\Util\JsonResponseTrait;
use AppBundle\Exception\DeveloperException;

/**
 * Class DataBroker is used to federate data produced by an asynchronous worker, back to the client
 * @package AppBundle\Job
 */
class DataBroker
{
    use JsonResponseTrait;

    /**
     * Redis client
     * @var Client
     */
    protected $client;

    /**
     * URI of the redis server
     * @var string
     */
    protected $redisServer;

    /**
     * Redis port
     * @var int
     */
    protected $redisPort;

    /**
     * Is the broker connected to redis
     * @var bool
     */
    protected $connected;

    /**
     * Unique job identifier
     * @var string
     */
    protected $jobId;

    /**
     * Data that the job may produce
     * @var mixed
     */
    protected $data;

    /**
     * @param string $redisServer URI of redis server
     * @param int $redisPort Numeric port number
     * @throws DeveloperException
     */
    public function __construct($redisServer, $redisPort)
    {
        if (empty($redisServer)) {
            throw new DeveloperException('Redis server URI is required');
        }

        if (empty($redisPort)) {
            throw new DeveloperException('Redis port number is required');
        }

        $this->redisServer = $redisServer;
        $this->redisPort = $redisPort;
    }

    /**
     * Connect to redis
     * @return void
     */
    public function connect()
    {
        if (!isset($this->connected)) {

            $this->client = new Client([
                'scheme' => 'tcp',
                'host' => $this->redisServer,
                'port' => $this->redisPort,
            ]);

            $this->connected = true;
        }
    }

    /**
     * Get data that the worker generated
     * @return mixed
     */
    public function get()
    {
        $data = $this->client->get($this->getKey());

        return $this->encodeJson($data);
    }

    /**
     * Worker will use this method to set data for this jobId
     * @return bool
     */
    public function save()
    {
        //If we have no data to save, lets not
        if (empty($this->data)) {
            return false;
        }

        // If the data we are trying to set is an array
        if (is_array($this->data)) {

            //Convert it to JSON
            $dataToSet = $this->encodeJson($this->data);
        } else {

            // if the data is not an array, put it in one, and convert to JSON
            $dataToSet = $this->encodeJson(array($this->data));
        }

        /**
         * This key is simply the jobId, prefixed with 'job-data' e.g. job-data-a1b2c3d4e5f6g7h8
         * @var string
         */
        $key = $this->getKey();

        return $this->client->set($key, $dataToSet);
    }

    /**
     * Get a namespaced key for worker data
     * @return string
     */
    protected function getKey()
    {
        return 'job-data-' . $this->jobId;
    }

    /**
     * @param mixed $data Data that the async process with set
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * @param string $jobId
     */
    public function setJobId($jobId)
    {
        $this->jobId = $jobId;
    }

    /**
     * Is the broker connected to redis
     * @return bool
     */
    public function isConnected()
    {
        return isset($this->connected) ? $this->connected : false;
    }
}