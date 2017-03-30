<?php
/**
 * Controller.php
 */
namespace Pails\Mvc;

use GuzzleHttp\Psr7\ServerRequest;
use League\OAuth2\Server\Exception\OAuthServerException;
use Pails\Exception;
use Psr\Http\Message\ResponseInterface;

/**
 * Class Controller
 *
 * @package Pails\Mvc
 *
 * @property \Pails\Plugins\ApiResponse                     $apiResponse
 * @property \Pails\Plugins\ApiClient                       $apiClient
 * @property \Pails\Plugins\Fractal                         $fractal
 * @property \Pails\Pluralizer                              $inflector
 * @property \Pails\Plugins\Config                          $config
 * @property \Pails\Exception\Handler                       $exceptionHandler
 * @property \Phalcon\Security\Random                       $random
 * @property \Phalcon\Cache\BackendInterface                $fileCache
 * @property \Phalcon\Cache\BackendInterface                $redisCache
 * @property \Phalcon\Cache\BackendInterface                $memcachedCache
 * @property \Phalcon\Cache\Multiple                        $cache
 * @property \Phalcon\Logger\Adapter\File                   $logger
 * @property \Phalcon\Logger\Adapter\File                   $errorLogger
 * @property \GuzzleHttp\Client                             $httpClient
 * @property \AliyunMNS\Client                              $mns
 * @property \OSS\OssClient                                 $oss
 * @property \League\Flysystem\FilesystemInterface          $storage
 * @property \League\Flysystem\FilesystemInterface          $localFs
 * @property \League\Flysystem\FilesystemInterface          $ossFs
 * @property \League\Flysystem\MountManager                 $filesystem
 * @property \League\OAuth2\Server\AuthorizationServer      $authServer
 * @property \League\OAuth2\Server\ResourceServer           $resourceServer
 * @property \Pails\OAuth2\StorageServiceInterface          $storageService
 * @property \League\OAuth2\Client\Provider\GenericProvider $authClient
 * @property \Redis                                         $redis
 * @property \Pails\Queue\Queue                             $queue
 */
abstract class Controller extends \Phalcon\Mvc\Controller
{
    /**
     * Preform oauth resource authenticate
     *
     * @throws \Pails\Exception
     *
     * @return \Psr\Http\Message\RequestInterface
     */
    public function authenticate()
    {
        $request = ServerRequest::fromGlobals();
        try {
            return $this->resourceServer->validateAuthenticatedRequest($request);
        } catch (OAuthServerException $exception) {
            $message = $exception->getMessage();
            if ($hint = $exception->getHint()) {
                $message .= ' (' . $hint . ')';
            }
            throw Exception::clientException($message, $exception->getHttpStatusCode(), $exception->getErrorType(), $exception->getHttpStatusCode());
        } catch (\Exception $exception) {
            throw Exception::serverException($exception->getMessage(), $exception->getCode());
        }
    }

    /**
     * Convert Psr7 Response to Phalcon's response
     *
     * @param ResponseInterface $response
     *
     * @return \Phalcon\Http\Response|\Phalcon\Http\ResponseInterface
     */
    protected function convertResponse(ResponseInterface $response)
    {
        $this->response->setContent($response->getBody());
        $this->response->setStatusCode($response->getStatusCode());
        foreach ($response->getHeaders() as $name => $values) {
            $this->response->setHeader($name, implode(', ', $values));
        }

        return $this->response;
    }
}
