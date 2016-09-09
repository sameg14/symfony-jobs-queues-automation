<?php

namespace AppBundle\Util;

use \Exception;
use AppBundle\Exception\DeveloperException;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class JsonResponseTrait helps with rendering standard JSON responses
 * @package CoreBundle\Util
 */
trait JsonResponseTrait
{
    /**
     * Convert data into json response
     * @param mixed $data Any kind of data i.e. array or object
     * @param array $headers Array of k v headers to send with response
     * @param int $httpStatusCode Must come from JsonResponse constants
     * @return JsonResponse
     */
    public function jsonResponse($data, $headers = array(), $httpStatusCode = JsonResponse::HTTP_OK)
    {
        $jsonResponse = new JsonResponse($data, $httpStatusCode, $headers);
        $jsonResponse->setStatusCode($httpStatusCode);
        $jsonResponse->setPublic();
        $jsonResponse->setEncodingOptions(JSON_UNESCAPED_SLASHES);
        $jsonResponse->headers->set('Content-Type', 'application/json');

        return $jsonResponse;
    }

    /**
     * Return a standard error response. Show stack trace on dev and test but not on prod
     * @param Exception $e The associated Exception that was thrown for this error
     * @param int $httpStatusCode HTTP Response code to send back in response header
     * @return JsonResponse
     */
    public function errorJsonResponse(Exception $e, $httpStatusCode = JsonResponse::HTTP_BAD_REQUEST)
    {
        $environment = getenv('SYMFONY_ENV');

        return $this->jsonResponse(
            array(
                'status_code' => 'ERROR',
                'status_message' => !empty($e->getMessage()) ? $e->getMessage() : 'Unknown error occurred',
                'stack_trace' => $environment != 'prod' ? $e->getTrace() : 'n/a'
            ),
            array(
                'status_code' => 'ERROR',
                'status_message' => !empty($e->getMessage()) ? $e->getMessage() : 'Unknown error occurred'
            ),
            $httpStatusCode
        );
    }

    /**
     * Convert a JSON string into an associative array
     * @param string $json Raw JSON string
     * @throws DeveloperException
     * @return array
     */
    public function decodeJson($json)
    {
        $error = null;
        $decodedJson = json_decode($json, $assoc = true, $depth = 512, JSON_UNESCAPED_SLASHES);

        switch (json_last_error()) {

            case JSON_ERROR_DEPTH:
                $error = 'Maximum stack depth exceeded';
                break;

            case JSON_ERROR_STATE_MISMATCH:
                $error = 'Underflow or the modes mismatch';
                break;

            case JSON_ERROR_CTRL_CHAR:
                $error = 'Unexpected control character found';
                break;

            case JSON_ERROR_SYNTAX:
                $error = 'Syntax error, malformed JSON';
                break;

            case JSON_ERROR_UTF8:
                $error = 'Malformed UTF-8 characters, possibly incorrectly encoded';
                break;
        }

        if (!empty($error)) {
            throw new DeveloperException($error . PHP_EOL . json_last_error_msg());
        }

        return $decodedJson;
    }

    /**
     * Return JSON representation of a value
     * @param mixed $value Array or object to convert to json
     * @throws DeveloperException
     * @return string
     */
    public function encodeJson($value)
    {
        $jsonResponse = $this->jsonResponse($value);

        return $jsonResponse->getContent();
    }
}