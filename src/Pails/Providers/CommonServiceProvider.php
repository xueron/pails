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
use Pails\Plugins\ApiClient;
use Pails\Plugins\ApiResponse;
use Pails\Plugins\Config;
use Pails\Plugins\Fractal;
use Pails\Pluralizer;
use Pails\Queue\Queue;
use Pails\Queue\Topic;

class CommonServiceProvider extends AbstractServiceProvider implements ServiceProviderInterface
{
    public function register()
    {
        $di = $this->getDI();

        // apiResponse
        $di->setShared(
            'apiResponse',
            ApiResponse::class
        );

        // apiClient
        $di->setShared(
            'apiClient',
            ApiClient::class
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
                /* @var \Pails\Container $this */
                if (!$this['config']->get('oss.enable', false)) {
                    throw new \LogicException('OSS is not enabled');
                }
                $endpoint = $this['config']->get('oss.endpoint');
                $accessId = $this['config']->get('oss.accessId');
                $accessKey = $this['config']->get('oss.accessKey');
                if (!$endpoint || !$accessId || !$accessKey) {
                    throw new \LogicException('请先配置MSN参数');
                }

                return new OssClient($accessId, $accessKey, $endpoint);
            }
        );

        // mns
        $di->setShared(
            'mns',
            function () {
                /* @var \Pails\Container $this */
                if (!$this['config']->get('mns.enable', false)) {
                    throw new \LogicException('MNS is not enabled');
                }
                $endpoint = $this['config']->get('mns.endpoint');
                $accessId = $this['config']->get('mns.accessId');
                $accessKey = $this['config']->get('mns.accessKey');
                if (!$endpoint || !$accessId || !$accessKey) {
                    throw new \LogicException('请先配置MSN参数');
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
                /* @var \Pails\Container $this */
                if (!$this['config']->get('oss.enable', false)) {
                    throw new \LogicException('OSS is not enabled');
                }
                $bucket = $this['config']->get('oss.bucket');
                if (!$bucket) {
                    throw new \LogicException('bucket is not set');
                }
                $adapter = new AliOSS($bucket, $this['oss']);

                return new Filesystem($adapter);
            }
        );

        // queue, Usage: $queue = $this->di->get('queue', 'queueName');
        $di->set(
            'queue',
            Queue::class
        );

        // topic, Usage: $topic = $this->di->get('topic', 'topicName');
        $di->set(
            'topic',
            Topic::class
        );

        // filesystem
        $di->setShared(
            'filesystem',
            function () {
                /* @var \Pails\Container $this */
                $fsList = [
                    'local' => $this['localFs'],
                ];
                if ($this['config']->get('oss.enable')) {
                    $fsList['oss'] = $this['ossFs'];
                }

                return new MountManager($fsList);
            }
        );

        // redis
        $di->setShared(
            'redis',
            function () {
                /* @var \Pails\Container $this */
                if (!$this['config']->get('redis.enable', false)) {
                    throw new \LogicException('redis is not enabled');
                }

                $host = $this['config']->get('redis.host', 'localhost');
                $port = $this['config']->get('redis.port', '6379');
                $auth = $this['config']->get('redis.auth');
                $persistent = $this['config']->get('redis.persistent');

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
                        throw new \RuntimeException('Failed to authenticate with the Redisd server');
                    }
                }

                return $redis;
            }
        );
    }
}
