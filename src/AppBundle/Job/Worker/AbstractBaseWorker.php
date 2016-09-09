<?php

namespace WorkerBundle\Job\Worker;

use AppBundle\Job\DataBroker;
use Symfony\Bridge\Monolog\Logger;
use AppBundle\Util\JsonResponseTrait;
use AppBundle\Exception\DeveloperException;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class AbstractBaseWorker will provide base functionality for all child workers to inherit
 * @package WorkerBundle\Job\Worker
 */
abstract class AbstractBaseWorker
{
    use JsonResponseTrait;

    /**
     * Array of arguments injected into this job
     * @var array
     */
    public $args;

    /**
     * Data broker is used to federate data from job to initiator
     * @var DataBroker
     */
    protected $broker;

    /**
     * Service container
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * Runs before the job starts executing
     * @return void
     */
    public function setup()
    {
        $this->requireAppKernel();

        $env = getenv('SYMFONY_ENV');

        $kernel = new \AppKernel($env, $debug = true);
        $kernel->boot();
        $container = $kernel->getContainer();
        $this->setContainer($container);

        // In case the worker needs to generate some data, the broker will be responsible for federating it
        $dataBroker = $this->getContainer()->get('service.data_broker');
        $this->setBroker($dataBroker);
        $this->broker->connect();
        $this->broker->setJobId($this->getJobId());
    }

    /**
     * The job server will execute the contents of this method contained in each child class
     * @return void
     */
    abstract public function perform();

    /**
     * Validation routine will get called "prior" to a worker initiating a unit of job
     * @return bool
     */
    abstract public function validate();

    /**
     * Require symfony's AppKernel class
     * @return void
     */
    private function requireAppKernel()
    {
        $pieces = explode("/src/", __DIR__);
        $basePath = array_shift($pieces);
        $kernelPath = $basePath . '/app/AppKernel.php';

        require_once($kernelPath);
    }

    /**
     * Run after perform, like closing resources, freeing up connections, writing out logs etc...
     * @return bool
     */
    public function tearDown()
    {
        return isset($this->broker) ? $this->broker->save() : false;
    }

    /**
     * Log a message to a file
     * @param mixed $msg Message to log
     * @return bool
     */
    public function log($msg)
    {
        if (!isset($this->logger)) {
            $this->logger = $this->container->get('logger');
        }

        return $this->logger->addDebug($msg);
    }

    /**
     * @param array $args
     */
    public function setArgs($args)
    {
        $this->args = $args;
    }

    /**
     * @return DataBroker
     */
    public function getBroker()
    {
        if (!$this->broker->isConnected()) {
            $this->broker->connect();
        }

        return $this->broker;
    }

    /**
     * @param DataBroker $broker
     */
    public function setBroker($broker)
    {
        $this->broker = $broker;
        $this->broker->setJobId($this->getJobId());
    }

    /**
     * @return ContainerInterface
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * @param ContainerInterface $container
     */
    public function setContainer($container)
    {
        $this->container = $container;
    }

    /**
     * @return Logger
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * @param Logger $logger
     */
    public function setLogger($logger)
    {
        $this->logger = $logger;
    }


    /**
     * Get the jobId for the currently running job
     * @return string|null
     */
    protected function getJobId()
    {
        return isset($this->job) && isset($this->job->payload) && isset($this->job->payload['id']) ? $this->job->payload['id'] : null;
    }

    /**
     * Get a payload headers
     * @return string|null
     */
    protected function getPayloadHeaders()
    {
        if (!empty($this->args) && sizeof($this->args) == 1) {
            $this->args = $this->args[0];
        }

        return !empty($this->args) && isset($this->args['headers']) ? $this->args['headers'] : null;
    }

    /**
     * Get a payload body
     * @return string|null
     */
    protected function getPayload()
    {
        if (!empty($this->args) && sizeof($this->args) == 1) {
            $this->args = $this->args[0];
        }

        return !empty($this->args) && isset($this->args['body']) ? $this->args['body'] : null;
    }

    /**
     * Get a bearer token from the authorization header
     * @throws DeveloperException
     * @return string|null
     */
    protected function getBearerToken()
    {
        $token = null;

        $payloadHeaders = $this->getPayloadHeaders();
        $headerData = $this->decodeJson($payloadHeaders);

        if (isset($headerData['authorization']) && !empty($headerData['authorization'][0])) {
            $auth = $headerData['authorization'][0];
            $pieces = explode("Bearer:", $auth);
            $token = array_pop($pieces);
            $token = trim($token);
        }

        return $token;
    }

    /**
     * Does this array have any string keys or is it a sequential array?
     * This method will return true for an associative array and false for a numeric sequentially indexed array
     * @param array $array
     * @return bool
     */
    protected function isAssociative(array $array)
    {
        return array_keys($array) !== range(0, count($array) - 1);
    }
}