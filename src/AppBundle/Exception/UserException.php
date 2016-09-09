<?php

namespace AppBundle\Exception;

/**
 * Class UserException is thrown when only a user needs notification of the error
 * @package AppBundle\Exception
 */
class UserException extends \Exception
{
    public static function emptyPayload()
    {
        throw new static('The payload is empty');
    }

    public static function invalidEmailAddress($emailAddress)
    {
        throw new static($emailAddress . " is an invalid email address, please try again");
    }
}