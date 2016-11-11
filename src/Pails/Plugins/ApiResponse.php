<?php
/**
 * ApiResponse.php
 *
 */
namespace Pails\Plugins;

use Phalcon\Mvc\User\Plugin;

class ApiResponse extends Plugin
{
    const CODE_WRONG_ARGS = 'GEN-WRONG-ARGS';

    const CODE_NOT_FOUND = 'GEN-NOT-FOUND';

    const CODE_INTERNAL_ERROR = 'GEN-INTERNAL-ERROR';

    const CODE_UNAUTHORIZED = 'GEN-UNAUTHORIZED';

    const CODE_FORBIDDEN = 'GEN-FORBIDDEN';

    const CODE_GONE = 'GEN-GONE';

    const CODE_METHOD_NOT_ALLOWED = 'GEN-METHOD-NOT-ALLOWED';

    const CODE_UNWILLING_TO_PROCESS = 'GEN-UNWILLING-TO-PROCESS';

    const CODE_UNPROCESSABLE = 'GEN-UNPROCESSABLE';

    /**
     * HTTP Status code
     *
     * @var int
     */
    protected $statusCode = 200;

    /**
     * Getter for statusCode
     *
     * @return int
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * Setter for status code
     *
     * @param int $statusCode
     * @return $this
     */
    public function setStatusCode($statusCode)
    {
        $this->statusCode = $statusCode;
        return $this;
    }


    public function response(array $data, array $headers = [])
    {
        $response = $this->getDI()->getShared('response');

        $response->setJsonContent($data, JSON_UNESCAPED_UNICODE);
        $response->setStatusCode($this->getStatusCode());
        foreach ($headers as $name => $value) {
            $response->setHeader($name, $value);
        }

        return $response;
    }

    public function withArray(array $array, array $headers = [])
    {
        $status = $this->getStatusCode() == 200;
        $data = array_merge([
            "status" => $status,
            "success" => $status,
            "code" => $this->getStatusCode(),
            "msg" => 'success',
        ], $array);
        return $this->response($data, $headers);
    }

    public function withError($message, $errorCode, array $headers = [])
    {
        $data = [
            "status" => false,
            "success" => false,
            "code" => $errorCode ?: $this->getStatusCode(),
            "msg" => $message,
            'error' => [
                'code' => $errorCode,
                'http_code' => $this->statusCode,
                'message' => $message
            ]
        ];
        return $this->response($data, $headers);
    }

    /**
     * Response for one item
     *
     * @param $data
     * @param callable|\League\Fractal\TransformerAbstract $transformer
     * @param array $meta
     * @param array $headers
     * @return mixed
     */
    public function withItem($data, $transformer, $meta = [], array $headers = [])
    {
        $data = $this->getDI()->get('fractal')->item($data, $transformer, null, $meta);

        return $this->withArray($data, $headers);
    }

    /**
     * Response for collection of items
     *
     * @param $data
     * @param callable|\League\Fractal\TransformerAbstract $transformer
     * @param array $meta
     * @param array $headers
     * @return mixed
     */
    public function withCollection($data, $transformer, $meta = [], array $headers = [])
    {
        $data = $this->getDI()->get('fractal')->collection($data, $transformer, null, null, $meta);

        return $this->withArray($data, $headers);
    }

    /**
     * Generates a response with a 403 HTTP header and a given message.
     *
     * @param string $message
     * @param array $headers
     * @return mixed
     */
    public function errorForbidden($message = 'Forbidden', array $headers = [])
    {
        return $this->setStatusCode(403)->withError($message, static::CODE_FORBIDDEN, $headers);
    }

    /**
     * Generates a response with a 500 HTTP header and a given message.
     *
     * @param string $message
     * @param array $headers
     * @return mixed
     */
    public function errorInternalError($message = 'Internal Error', array $headers = [])
    {
        return $this->setStatusCode(500)->withError($message, static::CODE_INTERNAL_ERROR, $headers);
    }

    /**
     * Generates a response with a 404 HTTP header and a given message.
     *
     * @param string $message
     * @param array $headers
     * @return mixed
     */
    public function errorNotFound($message = 'Resource Not Found', array $headers = [])
    {
        return $this->setStatusCode(404)->withError($message, static::CODE_NOT_FOUND, $headers);
    }

    /**
     * Generates a response with a 401 HTTP header and a given message.
     *
     * @param string $message
     * @param array $headers
     * @return mixed
     */
    public function errorUnauthorized($message = 'Unauthorized', array $headers = [])
    {
        return $this->setStatusCode(401)->withError($message, static::CODE_UNAUTHORIZED, $headers);
    }

    /**
     * Generates a response with a 400 HTTP header and a given message.
     *
     * @param string $message
     * @param array $headers
     * @return mixed
     */
    public function errorWrongArgs($message = 'Wrong Arguments', array $headers = [])
    {
        return $this->setStatusCode(400)->withError($message, static::CODE_WRONG_ARGS, $headers);
    }

    /**
     * Generates a response with a 410 HTTP header and a given message.
     *
     * @param string $message
     * @param array $headers
     * @return mixed
     */
    public function errorGone($message = 'Resource No Longer Available', array $headers = [])
    {
        return $this->setStatusCode(410)->withError($message, static::CODE_GONE, $headers);
    }

    /**
     * Generates a response with a 405 HTTP header and a given message.
     *
     * @param string $message
     * @param array $headers
     * @return mixed
     */
    public function errorMethodNotAllowed($message = 'Method Not Allowed', array $headers = [])
    {
        return $this->setStatusCode(405)->withError($message, static::CODE_METHOD_NOT_ALLOWED, $headers);
    }

    /**
     * Generates a Response with a 431 HTTP header and a given message.
     *
     * @param string $message
     * @param array $headers
     * @return mixed
     */
    public function errorUnwillingToProcess($message = 'Server is unwilling to process the request', array $headers = [])
    {
        return $this->setStatusCode(431)->withError($message, static::CODE_UNWILLING_TO_PROCESS, $headers);
    }

    /**
     * Generates a Response with a 422 HTTP header and a given message.
     *
     * @param string $message
     * @param array $headers
     * @return mixed
     */
    public function errorUnprocessable($message = 'Unprocessable Entity', array $headers = [])
    {
        return $this->setStatusCode(422)->withError($message, static::CODE_UNPROCESSABLE, $headers);
    }
}
