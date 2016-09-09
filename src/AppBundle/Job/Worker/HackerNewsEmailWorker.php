<?php

namespace AppBundle\Job\Worker;

use AppBundle\Exception\DeveloperException;
use WorkerBundle\Job\Worker\AbstractBaseWorker;

/**
 * Class HackerNewsEmailWorker will email you a list of hacker news articles
 * @package AppBundle\Job\Worker
 */
class HackerNewsEmailWorker extends AbstractBaseWorker
{
    /**
     * Save a JSON payload from a device out in the field to AWS dynamodb
     * @throws DeveloperException
     * @return void
     */
    public function perform()
    {
        $payload = $this->getPayload();

        $this->broker->setData($this->responseData);
    }

    /**
     * Ensure that the payload is valid
     * @return bool
     */
    public function validate()
    {
        /**
         * JSON Payload from the devices out in the field. Can be an associative array or an array of assoc arrays
         * @var string
         */
        $payload = $this->getPayload();
        if (empty($payload)) {
            DeveloperException::emptyPayload();
        }

        return true;
    }
}