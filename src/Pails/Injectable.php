<?php
/**
 * Injectable.php
 *
 * A wrap for phalcon's injectable abstract. Add some properties.
 */
namespace Pails;

/**
 * Class Injectable
 *
 * @package Pails
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
 * @property \Pails\Queue\Topic                             $topic
 *
 * @method \Phalcon\Logger\AdapterInterface error($message, array $context = [])
 * @method \Phalcon\Logger\AdapterInterface info($message, array $context = [])
 * @method \Phalcon\Logger\AdapterInterface debug($message, array $context = [])
 * @method \Phalcon\Logger\AdapterInterface notice($message, array $context = [])
 * @method \Phalcon\Logger\AdapterInterface critical($message, array $context = [])
 * @method \Phalcon\Logger\AdapterInterface emergency($message, array $context = [])
 * @method \Phalcon\Logger\AdapterInterface warning($message, array $context = [])
 * @method \Phalcon\Logger\AdapterInterface alert($message, array $context = [])
 */
abstract class Injectable extends \Phalcon\Di\Injectable
{
    public function __call($method, $args)
    {
        // 处理需要日志的信息
        $message = array_shift($args);
        if (is_resource($message)) {
            $message = sprintf('resource-type=%s', get_resource_type($message));
        } elseif (is_object($message)) {
            $message = var_export($message, 1);
        } else {
            if (!is_scalar($message)) {
                $message = serialize($message);
            }
        }

        // 获取debug信息
        $file = '';
        $line = '';
        $class = static::class;
        $function = '';
        $trace = debug_backtrace();
        array_shift($trace);
        $_first = array_shift($trace);
        if ($_first) {
            $file = $_first['file'];
            $line = $_first['line'];
            $class = $_first['class'];
            $function = $_first['function'];
        }

        if (defined('APP_DEBUG') && APP_DEBUG) {
            $message = sprintf('[%s::%s] %s [file=%s, line=%s]', $class, $function, $message, $file, $line);
        } else {
            $message = sprintf('[%s::%s] %s', $class, $function, $message);
        }
        array_unshift($args, $message);

        try {
            return call_user_func_array([$this->logger, $method], $args);
        } catch (\Exception $e) {
            error_log($message);
        }
    }
}
