<?php
namespace Pails\Providers;

use AliyunMNS\Client as MnsClient;
use GuzzleHttp\Client as HttpClient;
use League\Flysystem\Adapter\Local as LocalAdapter;
use League\Flysystem\Filesystem;
use League\Flysystem\MountManager;
use OSS\OssClient;
use Pails\Exception\Handler;
use Pails\Plugins\AliOSS;
use Pails\Plugins\ApiResponse;
use Pails\Plugins\Config;
use Pails\Plugins\Fractal;
use Pails\Pluralizer;
use Pails\Queue\Queue;

class CommonServiceProvider extends AbstractServiceProvider implements ServiceProviderInterface
{
    function register()
    {
        $di = $this->getDI();

        // apiResponse
        $di->setShared(
            'apiResponse',
            ApiResponse::class
        );

        // config
        $di->setShared(
            'config',
            Config::class
        );

        // fractal
        $di->setShared(
            'fractal',
            Fractal::class
        );

        // inflector
        $di->setShared(
            'inflector',
            Pluralizer::class
        );

        // httpClient
        $di->setShared(
            'httpClient',
            HttpClient::class
        );

        // exceptionHandler
        $di->setShared(
            'exceptionHandler',
            Handler::class
        );

        // oss
        $di->setShared(
            'oss',
            function () {
                $endpoint = $this->get("config")->get("oss.endpoint");
                $accessId = $this->get("config")->get("oss.accessId");
                $accessKey = $this->get("config")->get("oss.accessKey");
                if (!$endpoint || !$accessId || !$accessKey) {
                    throw new \LogicException("请先配置MSN参数");
                }
                return new OssClient($accessId, $accessKey, $endpoint);
            }
        );

        // mns
        $di->setShared(
            'mns',
            function () {
                $endpoint = $this->get("config")->get("mns.endpoint");
                $accessId = $this->get("config")->get("mns.accessId");
                $accessKey = $this->get("config")->get("mns.accessKey");
                if (!$endpoint || !$accessId || !$accessKey) {
                    throw new \LogicException("请先配置MSN参数");
                }
                $client = new MnsClient($endpoint, $accessId, $accessKey);
                return $client;
            }
        );

        // localFs
        $di->setShared(
            'localFs',
            function () {
                $adapter = new LocalAdapter($this->storagePath());
                return new Filesystem($adapter);
            }
        );

        // ossFs
        $di->setShared(
            'ossFs',
            function () {
                $bucket = $this->get('config')->get('oss.bucket');
                $adapter =  new AliOSS($bucket, $this['oss']);
                return new Filesystem($adapter);
            }
        );

        // queue
        $di->set(
            'queue',
            Queue::class
        );

        // filesystem
        $di->setShared(
            'filesystem',
            function () {
                $fsList = [
                    'local' => $this['localFs']
                ];
                if ($this->get("config")->get('oss.enable')) {
                    $fsList['oss'] = $this['ossFs'];
                }
                return new MountManager($fsList);
            }
        );

        // redis
        $di->setShared(
            'redis',
            function () {
                $host = $this->get('config')->get('redis.host', 'localhost');
                $port = $this->get('config')->get('redis.port', '6379');
                $auth = $this->get('config')->get('redis.auth');
                $persistent = $this->get('config')->get('redis.persistent');

                $redis = new \Redis();
                $ok = false;
                if ($persistent) {
                    $ok = $redis->pconnect($host, $port);
                } else {
                    $ok = $redis->connect($host, $port);
                }
                if (!$ok) {
                    throw new \RuntimeException("Could not connect to the Redisd server $host:$port");
                }
                if ($auth) {
                    $ok = $redis->auth($auth);
                    if (!$ok) {
                        throw new \RuntimeException("Failed to authenticate with the Redisd server");
                    }
                }
                return $redis;
            }
        );
    }
}
