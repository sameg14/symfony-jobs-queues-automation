<?php

namespace AppBundle\Service;

/**
 * Class HackerNewsService
 * @package AppBundle\Service
 */
class HackerNewsService
{
    const STORY_IDS_ENDPOINT = 'https://hacker-news.firebaseio.com/v0/askstories.json?print=pretty';

    const STORY_DETAIL_ENDPOINT = 'https://hacker-news.firebaseio.com/v0/item/{storyId}.json?print=pretty';

    public function __construct()
    {
    }

    public function getLatestStories()
    {

    }

    public function getStory($storyId)
    {

    }
}
