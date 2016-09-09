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
        $mailer = $this->getContainer()->get('mailer');
        $template = $this->getContainer()->get('templating');
        $hackerNewsService = $this->getContainer()->get('service.hacker_news');

        $stories = [];
        $payload = $this->getPayload();
        $toEmail = $payload['email'];
        $fromEmail = $this->getContainer()->getParameter('from_email');

        $storyIds = $hackerNewsService->getLatestStories();
        if (empty($storyIds)) {
            throw new DeveloperException('No stories found');
        }

        foreach ($storyIds as $storyId) {
            $stories[] = $hackerNewsService->getOneStory($storyId);
        }

        $body = $template->render('AppBundle:Demo:email.hackernews.html.twig', [
            'stories' => $stories
        ]);

        $message = \Swift_Message::newInstance()
            ->setSubject('Daily hacker news summary')
            ->setFrom($fromEmail)
            ->setTo($toEmail)
            ->setBody($body)
            ->setContentType('text/html');
        
        $didSend = $mailer->send($message);

        $this->broker->setData([
            'emailAddress' => $payload,
            'storyIds' => $storyIds,
            'didSend' => $didSend
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

        if (!filter_var($payload['email'], FILTER_VALIDATE_EMAIL)) {
            UserException::invalidEmailAddress($payload);
        }

        return true;
    }
}