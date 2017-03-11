<?php
/**
 * Controller.php
 *
 */
namespace Pails\Mvc;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use League\OAuth2\Server\Exception\OAuthServerException;
use Psr\Http\Message\ResponseInterface;

/**
 * Class Controller
 * @package Pails\Mvc
 * @property \Pails\Plugins\ApiResponse $apiResponse
 * @property \Pails\Plugins\Fractal $fractal
 * @property \Pails\Pluralizer $inflector
 * @property \Pails\Plugins\Config $config
 * @property \Pails\Exception\Handler $exceptionHandler
 * @property \Phalcon\Security\Random $random
 * @property \Phalcon\Cache\BackendInterface $fileCache
 * @property \Phalcon\Cache\BackendInterface $redisCache
 * @property \Phalcon\Cache\BackendInterface $memcachedCache
 * @property \Phalcon\Cache\Multiple $cache
 * @property \Phalcon\Logger\Adapter\File $logger
 * @property \Phalcon\Logger\Adapter\File $errorLogger
 * @property \GuzzleHttp\Client $httpClient
 * @property \AliyunMNS\Client $mns
 * @property \OSS\OssClient $oss
 * @property \League\Flysystem\FilesystemInterface $localFs
 * @property \League\Flysystem\FilesystemInterface $ossFs
 * @property \League\Flysystem\MountManager $filesystem
 * @property \League\OAuth2\Server\AuthorizationServer $authServer
 * @property \League\OAuth2\Server\ResourceServer $resourceServer
 * @property \Pails\OAuth2\StorageServiceInterface $storageService
 * @property \League\OAuth2\Client\Provider\GenericProvider $authClient
 * @property \Redis $redis
 * @property \Pails\Queue\Queue $queue
 */
abstract class Controller extends \Phalcon\Mvc\Controller
{
    /**
     * Preform oauth resource authenticate
     *
     * @return bool|\Phalcon\Http\Response|\Phalcon\Http\ResponseInterface
     */
    public function authenticate()
    {
        $request = ServerRequest::fromGlobals();
        $response = new Response();
        try {
            $this->resourceServer->validateAuthenticatedRequest($request);
            return true;
        } catch (OAuthServerException $exception) {
            return $this->convertResponse($exception->generateHttpResponse($response));
        } catch (\Exception $exception) {
            return $this->convertResponse(
                (new OAuthServerException($exception->getMessage(), 0, 'unknown_error', 500))
                ->generateHttpResponse($response));
        }
    }

    /**
     * Convert Psr7 Response to Phalcon's response
     *
     * @param ResponseInterface $response
     * @return \Phalcon\Http\Response|\Phalcon\Http\ResponseInterface
     */
    protected function convertResponse(ResponseInterface $response)
    {
        $this->response->setContent($response->getBody());
        $this->response->setStatusCode($response->getStatusCode());
        foreach ($response->getHeaders() as $name => $value) {
            $this->response->setHeader($name, $value);
        }
        return $this->response;
    }
}
