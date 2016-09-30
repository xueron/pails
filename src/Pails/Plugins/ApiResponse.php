<?php
/**
 * ApiResponse.php
 *
 */
namespace Pails\Plugins;


use EllipseSynergie\ApiResponse\AbstractResponse;
use Pails\Exception;
use Phalcon\Di\InjectionAwareInterface;
use Phalcon\DiInterface;

class ApiResponse extends AbstractResponse implements InjectionAwareInterface
{
    /**
     * @var DiInterface
     */
    protected $_dependencyInjector = null;

    public function setDI(DiInterface $dependencyInjector)
    {
        $this->_dependencyInjector = $dependencyInjector;
    }

    public function getDI()
    {
        return $this->_dependencyInjector;
    }

    public function response(array $data, array $headers = [])
    {
        $di = $this->_dependencyInjector;
        if (!is_object($di)) {
            throw new Exception("A dependency injection container is required to access the 'response' service");
        }
        $response = $di->getShared('response');

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
        $data = [
            "status" => $status,
            "success" => $status,
            "code" => $this->getStatusCode(),
            "msg" => 'success',
            "data" => $array
        ];
        return $this->response($data, $headers);
    }

    public function withError($message, $errorCode, array $headers = [])
    {
        $data = [
            "status" => false,
            "success" => false,
            "code" => $errorCode ?: $this->getStatusCode(),
            "msg" => $message,
            "data" => [],
            'error' => [
                'code' => $errorCode,
                'http_code' => $this->statusCode,
                'message' => $message
            ]
        ];
        return $this->response($data, $headers);
    }
}
