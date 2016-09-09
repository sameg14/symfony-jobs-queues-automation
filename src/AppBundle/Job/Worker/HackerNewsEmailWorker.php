<?php

namespace AppBundle\Job\Worker;

use AppBundle\Exception\UserException;
use AppBundle\Exception\DeveloperException;

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
        $stories = [];
        $payload = $this->getPayload();
        $hackerNewsService = $this->getContainer()->get('service.hacker_news');

        $storyIds = $hackerNewsService->getLatestStories();

        if (empty($storyIds)) {
            throw new DeveloperException('No stories found');
        }

        foreach ($stories as $storyId) {
            $stories[] = $hackerNewsService->getOneStory($storyId);
        }

        $this->broker->setData([
            'emailAddress' => $payload,
            'storyIds' => $storyIds
        ]);
    }

    /**
     * Ensure that the payload is valid and contains only an email address
     * @throws UserException
     * @return bool
     */
    public function validate()
    {
        $payload = $this->getPayload();
        if (empty($payload)) {
            UserException::emptyPayload();
        }

        if (!filter_var($payload, FILTER_VALIDATE_EMAIL)) {
            UserException::invalidEmailAddress($payload);
        }

        return true;
    }
}