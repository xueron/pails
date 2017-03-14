<?php
/**
 * Exception.php
 */


namespace Pails;

/**
 * Class Exception
 *
 * @package Pails
 */
class Exception extends \Phalcon\Exception
{
    /**
     * @var int
     */
    protected $httpStatusCode;

    /**
     * @var \Exception
     */
    protected $errorType;

    /**
     * Exception constructor.
     *
     * @param string $message
     * @param int    $code
     * @param string $errorType
     * @param int    $httpStatusCode
     */
    public function __construct($message, $code, $errorType, $httpStatusCode = 400)
    {
        parent::__construct($message, $code);
        $this->httpStatusCode = $httpStatusCode;
        $this->errorType = $errorType;
    }

    /**
     * @param string $message
     * @param int    $code
     * @param string $errorType
     * @param int    $httpStatusCode
     *
     * @return static
     */
    public static function clientException($message = 'Bad Request', $code = 400, $errorType = 'client_error', $httpStatusCode = 400)
    {
        return new static($message, $code, $errorType, $httpStatusCode);
    }

    /**
     * @param string $message
     * @param int    $code
     * @param string $errorType
     * @param int    $httpStatusCode
     *
     * @return static
     */
    public static function serverException($message = 'Internal Server Error', $code = 500, $errorType = 'server_error', $httpStatusCode = 500)
    {
        return new static($message, $code, $errorType, $httpStatusCode);
    }

    /**
     * @return int
     */
    public function getHttpStatusCode()
    {
        return $this->httpStatusCode;
    }

    /**
     * @return \Exception|string
     */
    public function getErrorType()
    {
        return $this->errorType;
    }
}
