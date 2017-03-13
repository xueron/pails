<?php
/**
 * ApiResponse.php
 */
namespace Pails\Plugins;

use Pails\Injectable;

class ApiResponse extends Injectable
{
    /**
     * Common error code
     */
    const CODE_WRONG_ARGS = 400;
    const CODE_UNAUTHORIZED = 401;
    const CODE_FORBIDDEN = 403;
    const CODE_NOT_FOUND = 404;
    const CODE_METHOD_NOT_ALLOWED = 405;
    const CODE_GONE = 410;
    const CODE_UNPROCESSABLE = 422;
    const CODE_UNWILLING_TO_PROCESS = 431;
    const CODE_INTERNAL_ERROR = 500;

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
     *
     * @return $this
     */
    public function setStatusCode($statusCode)
    {
        $this->statusCode = $statusCode;

        return $this;
    }

    /**
     * @param array $data
     * @param array $headers
     *
     * @return mixed
     */
    public function response(array $data, array $headers = [])
    {
        $response = $this->response;
        $response->setJsonContent($data, JSON_UNESCAPED_UNICODE);
        $response->setStatusCode($this->getStatusCode());
        foreach ($headers as $name => $value) {
            $response->setHeader($name, $value);
        }

        return $response;
    }

    /**
     * @param array $array
     * @param array $headers
     *
     * @return mixed
     */
    public function withArray(array $array, array $headers = [])
    {
        $status = $this->getStatusCode() == 200;
        $data = array_merge([
            'status' => $status,
            'success' => $status,
            'code' => $this->getStatusCode(),
            'msg' => 'success',
        ], $array);

        return $this->response($data, $headers);
    }

    /**
     * 出错的返回.
     *
     * 我们约定：
     *     5XX 为服务器端的内部错误，如数据库存取失败等等；
     *     4XX 是客户端引起的错误，如参数不对、没有授权等等；
     *         更多的状态码，可以自定义；
     *
     * @param $message
     * @param $errorCode
     * @param array $headers
     *
     * @return mixed
     */
    public function withError($message, $errorCode = 500, array $headers = [])
    {
        $data = [
            'status' => false,
            'success' => false,
            'code' => $errorCode ?: $this->getStatusCode(),
            'msg' => $message,
            'error' => [
                'code' => $errorCode,
                'http_code' => $this->statusCode,
                'message' => $message,
            ],
        ];

        return $this->response($data, $headers);
    }

    /**
     * 成功的返回
     *
     * @param string $message
     * @param array  $headers
     *
     * @return mixed
     */
    public function withSuccess($message = 'success', array $headers = [])
    {
        $data = [
            'status' => true,
            'success' => true,
            'code' => $this->getStatusCode(),
            'msg' => $message,
        ];

        return $this->response($data, $headers);
    }

    /**
     * 成功的返回, 携带有效数据
     *
     * @param mixed $data
     * @param array $headers
     *
     * @return mixed
     */
    public function withData($data, array $headers = [])
    {
        return $this->withArray([
            'data' => $data,
        ], $headers);
    }

    /**
     * Response for one item
     *
     * @param $data
     * @param callable|\League\Fractal\TransformerAbstract $transformer
     * @param array                                        $meta
     * @param array                                        $headers
     *
     * @return mixed
     */
    public function withItem($data, $transformer, $meta = [], array $headers = [])
    {
        $data = $this->fractal->item($data, $transformer, null, $meta);

        return $this->withArray($data, $headers);
    }

    /**
     * Response for collection of items
     *
     * @param $data
     * @param callable|\League\Fractal\TransformerAbstract $transformer
     * @param array                                        $meta
     * @param array                                        $headers
     *
     * @return mixed
     */
    public function withCollection($data, $transformer, $meta = [], array $headers = [])
    {
        $data = $this->fractal->collection($data, $transformer, null, null, $meta);

        return $this->withArray($data, $headers);
    }

    /**
     * Generates a response with a 403 HTTP header and a given message.
     *
     * @param string $message
     * @param array  $headers
     *
     * @return mixed
     */
    public function errorForbidden($message = 'Forbidden', array $headers = [])
    {
        return $this->withError($message, static::CODE_FORBIDDEN, $headers);
    }

    /**
     * Generates a response with a 500 HTTP header and a given message.
     *
     * @param string $message
     * @param array  $headers
     *
     * @return mixed
     */
    public function errorInternalError($message = 'Internal Error', array $headers = [])
    {
        return $this->withError($message, static::CODE_INTERNAL_ERROR, $headers);
    }

    /**
     * Generates a response with a 404 HTTP header and a given message.
     *
     * @param string $message
     * @param array  $headers
     *
     * @return mixed
     */
    public function errorNotFound($message = 'Resource Not Found', array $headers = [])
    {
        return $this->withError($message, static::CODE_NOT_FOUND, $headers);
    }

    /**
     * Generates a response with a 401 HTTP header and a given message.
     *
     * @param string $message
     * @param array  $headers
     *
     * @return mixed
     */
    public function errorUnauthorized($message = 'Unauthorized', array $headers = [])
    {
        return $this->withError($message, static::CODE_UNAUTHORIZED, $headers);
    }

    /**
     * Generates a response with a 400 HTTP header and a given message.
     *
     * @param string $message
     * @param array  $headers
     *
     * @return mixed
     */
    public function errorWrongArgs($message = 'Wrong Arguments', array $headers = [])
    {
        return $this->withError($message, static::CODE_WRONG_ARGS, $headers);
    }

    /**
     * Generates a response with a 410 HTTP header and a given message.
     *
     * @param string $message
     * @param array  $headers
     *
     * @return mixed
     */
    public function errorGone($message = 'Resource No Longer Available', array $headers = [])
    {
        return $this->withError($message, static::CODE_GONE, $headers);
    }

    /**
     * Generates a response with a 405 HTTP header and a given message.
     *
     * @param string $message
     * @param array  $headers
     *
     * @return mixed
     */
    public function errorMethodNotAllowed($message = 'Method Not Allowed', array $headers = [])
    {
        return $this->withError($message, static::CODE_METHOD_NOT_ALLOWED, $headers);
    }

    /**
     * Generates a Response with a 431 HTTP header and a given message.
     *
     * @param string $message
     * @param array  $headers
     *
     * @return mixed
     */
    public function errorUnwillingToProcess($message = 'Server is unwilling to process the request', array $headers = [])
    {
        return $this->withError($message, static::CODE_UNWILLING_TO_PROCESS, $headers);
    }

    /**
     * Generates a Response with a 422 HTTP header and a given message.
     *
     * @param string $message
     * @param array  $headers
     *
     * @return mixed
     */
    public function errorUnprocessable($message = 'Unprocessable Entity', array $headers = [])
    {
        return $this->withError($message, static::CODE_UNPROCESSABLE, $headers);
    }
}
