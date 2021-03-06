<?php

namespace AppBundle\Service;

use GuzzleHttp\Client;
use AppBundle\Util\JsonResponseTrait;

/**
 * Class HackerNewsService will provider functionality to interact with hacker news articles over the wire
 * @package AppBundle\Service
 */
class HackerNewsService
{
    use JsonResponseTrait;

    const STORY_IDS_ENDPOINT = 'https://hacker-news.firebaseio.com/v0/askstories.json?print=pretty';

    const STORY_DETAIL_ENDPOINT = 'https://hacker-news.firebaseio.com/v0/item/{storyId}.json?print=pretty';

    /**
     * Guzzle HTTP client
     * @var Client
     */
    protected $client;

    /**
     * HackerNewsService constructor.
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * Get a list of all the latest stories on hacker news
     * @return array
     */
    public function getLatestStories()
    {
        $response = $this->client->get(self::STORY_IDS_ENDPOINT);

        $stories = $response->getBody()->getContents();
        
        return $this->decodeJson($stories);
    }

    /**
     * Get data for a particular story
     * @param int $storyId Primary key for a particular story
     * @return array
     */
    public function getOneStory($storyId)
    {
        $url = str_replace('{storyId}', $storyId, self::STORY_DETAIL_ENDPOINT);

        $response = $this->client->get($url);

        $story = $response->getBody()->getContents();
        $story = $this->decodeJson($story);

        return [
            'title' => isset($story['title']) ? $story['title'] : null,
            'text' => isset($story['text']) ? $story['text'] : null
        ];
    }
}
