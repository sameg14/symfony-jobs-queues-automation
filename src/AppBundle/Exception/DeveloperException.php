<?php

namespace AppBundle\Exception;

/**
 * Class DeveloperException gets thrown when the dev team needs to act on an error
 * @package AppBundle\Exception
 */
class DeveloperException extends \Exception
{

    public static function emptyPayload()
    {
        throw new static('The payload is empty');
    }
}