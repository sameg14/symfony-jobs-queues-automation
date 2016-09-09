<?php

namespace AppBundle\Job;

use \Resque;
use \Resque_Worker;
use \Resque_Job_Status;
use Symfony\Component\DependencyInjection\ContainerInterface;
use WorkerBundle\Job\Worker\AbstractWorker;

/**
 * Class JobRunner will run specific workers
 * @package AppBundle\Job
 */
class JobRunner
{
    const JOB_NAMESPACE = 'AppBundle\\Worker';

    /**
     * Name of queue, default is default
     * @var string
     */
    protected $queue = 'default';

    /**
     * Fully name spaced job class
     * @var string
     */
    protected $workerClass;

    /**
     * Array of any data you want to send to the job
     * @var array
     */
    protected $jobData = array();

    /**
     * JobId from a newly created job
     * @var int
     */
    protected $jobId;

    /**
     * URI of redis server
     * @var string
     */
    protected $redisServer;

    /**
     * Port number
     * @var int
     */
    protected $redisPort;

    /**
     * Symfony service container
     * @var ContainerInterface
     */
    protected $container;

    /**
     * Used to federate data produced back from a job to the requesting client
     * @var DataBroker
     */
    protected $dataBroker;

    /**
     * JobRunner constructor.
     * @param string $redisServer
     * @param int $redisPort
     * @param DataBroker $dataBroker
     */
    public function __construct($redisServer, $redisPort, $dataBroker)
    {
        $this->redisServer = $redisServer;
        $this->redisPort = $redisPort;
        $this->dataBroker = $dataBroker;

        Resque::setBackend($redisServer . ':' . $redisPort);
    }

    /**
     * Schedule a job i.e. add it to the redis backed queue
     * @return int jobId from the newly created job
     */
    public function schedule()
    {
        /** @var  AbstractWorker $obj */
        $obj = new $this->workerClass();
        $obj->setContainer($this->container);
        $obj->args = $this->jobData;
        $obj->setUp();
        $obj->validate();

        return $this->jobId = Resque::enqueue(
            $this->queue, $this->workerClass, $this->jobData, $trackStatus = true
        );
    }

    /**
     * Get status of a job.
     * @return string
     */
    public function getStatus()
    {
        $job = new Resque_Job_Status($this->getJobId());

        $jobStatusCode = $job->get();

        switch ($jobStatusCode) {

            case Resque_Job_Status::STATUS_WAITING:
                return 'Waiting';
                break;

            case Resque_Job_Status::STATUS_COMPLETE:
                return 'Complete';
                break;

            case Resque_Job_Status::STATUS_FAILED:
                return "Failed";
                break;

            case Resque_Job_Status::STATUS_RUNNING:
                return 'Running';
                break;

            default:
                return 'Unknown! Code: ' . $jobStatusCode;
        }
    }

    /**
     * Get any data that the worker set using DataBroker
     * @return mixed
     */
    public function getReturnedData()
    {
        $broker = $this->getDataBroker();
        $broker->setJobId($this->getJobId());

        return $broker->get();
    }

    /**
     * Get a list of all queues from redis.
     * @return array Array of queues.
     */
    public function getQueues()
    {
        return Resque::queues();
    }

    /**
     * How many jobs are currently in a queue?
     * @return int how many jobs are in the queue
     */
    public function size()
    {
        return Resque::size($this->queue);
    }

    /**
     * @return mixed
     */
    public function getWorkers()
    {
        return Resque_Worker::all();
    }

    /**
     * @param string $workerClass just the non namespaced worker clss
     */
    public function setWorkerClass($workerClass)
    {
        $this->workerClass = __NAMESPACE__ . '\\Worker\\' . $workerClass;
    }

    /**
     * @return string
     */
    public function getWorkerClass()
    {
        return $this->workerClass;
    }

    /**
     * @param array $jobData
     */
    public function setJobData($jobData)
    {
        if (!empty($jobData) && !is_array($jobData)) {
            $jobData = array($jobData);
        }
        $this->jobData = $jobData;
    }

    /**
     * @return array
     */
    public function getJobData()
    {
        return $this->jobData;
    }

    /**
     * @param int $jobId
     */
    public function setJobId($jobId)
    {
        $this->jobId = $jobId;
    }

    /**
     * @return int
     */
    public function getJobId()
    {
        return $this->jobId;
    }

    /**
     * @param string $queue
     */
    public function setQueue($queue)
    {
        $this->queue = $queue;
    }

    /**
     * @return string
     */
    public function getQueue()
    {
        return $this->queue;
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
     * @return DataBroker
     */
    public function getDataBroker()
    {
        if (!$this->dataBroker->isConnected()) {
            $this->dataBroker->connect();
        }

        return $this->dataBroker;
    }
}