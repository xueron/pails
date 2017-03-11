<?php
namespace Pails\Providers;

use Pails\Plugins\VoltExtension;
use Phalcon\Annotations\Adapter\Memory as MemoryAnnotations;
use Phalcon\Annotations\Adapter\Files as FileAnnotations;
use Phalcon\Cache\Backend\File as FileCache;
use Phalcon\Cache\Backend\Redis as RedisCache;
use Phalcon\Cache\Backend\Libmemcached as MemcachedCache;
use Phalcon\Cache\Frontend\Data as DataFrontend;
use Phalcon\Cache\Multiple as MultipleCache;
use Phalcon\Cache\Frontend\Output as OutputFrontend;
use Phalcon\Crypt;
use Phalcon\Events\ManagerInterface;
use Phalcon\Http\Response\Cookies;
use Phalcon\Logger\Adapter\File as FileLogger;
use Phalcon\Mvc\Dispatcher;
use Phalcon\Mvc\Model\Metadata\Files as FileMetaData;
use Phalcon\Mvc\Model\MetaData\Memory as MemoryMetaData;
use Phalcon\Mvc\Url;
use Phalcon\Mvc\View;
use Phalcon\Mvc\View\Engine\Volt;
use Phalcon\Security\Random;
use Phalcon\Session\Adapter\Files as FileSession;
use Phalcon\Session\Adapter\Libmemcached as MemcachedSession;
use Phalcon\Session\Adapter\Redis as RedisSession;

class PailsServiceProvider extends AbstractServiceProvider
{
    function register()
    {
        $di = $this->di;

        // annotations
        $di->setShared(
            'annotations',
            function () {
                if ($this->environment() == 'production') {
                    $annotationsDir = $this->tmpPath() . '/cache/routes/';
                    if (!file_exists($annotationsDir)) {
                        @mkdir($annotationsDir, 0755, true);
                    }
                    return new FileAnnotations([
                        "annotationsDir" => $annotationsDir
                    ]);
                }
                return new MemoryAnnotations();
            }
        );

        // fileCache
        $di->setShared(
            'fileCache',
            function () {
                $frontCache = new DataFrontend([
                    "lifetime" => $this['config']->get('cache.file.lifetime', 86400),
                ]);

                $cachePath = $this->tmpPath() . '/cache/data/';
                if (!file_exists($cachePath)) {
                    @mkdir($cachePath, 0755, true);
                }
                $cache = new FileCache(
                    $frontCache,
                    [
                        "prefix" => $this['config']->get('cache.file.prefix'),
                        "cacheDir" => $cachePath
                    ]
                );
                return $cache;
            }
        );

        // redisCache
        $di->setShared(
            'redisCache',
            function () {
                if (!$this['config']->get('cache.redis.enable', false)) {
                    throw new \LogicException("redis cache is not enabled");
                }

                $frontCache = new DataFrontend([
                    "lifetime" => $this['config']->get('cache.redis.lifetime', 3600),
                ]);

                $options = [
                    "prefix" => $this['config']->get('cache.redis.prefix'),
                    "host" => $this['config']->get('cache.redis.host'),
                    "port" => $this['config']->get('cache.redis.port'),
                    "persistent" => $this['config']->get('cache.redis.persistent'),
                    "statsKey" => '_PHCR',
                ];
                if ($auth = $this['config']->get('cache.redis.auth')) {
                    $options['auth'] = $auth;
                }

                $cache = new RedisCache($frontCache, $options);
                return $cache;
            }
        );

        // memcachedCache
        $di->setShared(
            'memcachedCache',
            function () {
                if (!$this['config']->get('cache.memcache.enable', false)) {
                    throw new \LogicException("memcache cache is not enabled");
                }

                $frontCache = new DataFrontend([
                    "lifetime" => $this['config']->get('cache.memcache.lifetime', 3600),
                ]);

                $cache = new MemcachedCache(
                    $frontCache,
                    [
                        'servers' => [
                            "host" => $this['config']->get('cache.memcache.host'),
                            "port" => $this['config']->get('cache.memcache.port'),
                            "weight" => 1
                        ],
                        'client' => [
                            \Memcached::OPT_HASH => \Memcached::HASH_MD5,
                            \Memcached::OPT_PREFIX_KEY => 'prefix.',
                        ],
                        "prefix" => $this['config']->get('cache.memcache.prefix'),
                        "statsKey" => '_PHCM',
                    ]
                );
                return $cache;
            }
        );

        // cache, 多层Cache设置
        $di->setShared(
            'cache',
            function () {
                $backends = [
                    $this['fileCache']
                ];
                if ($this['config']->get('cache.redis.enable', false)) {
                    array_unshift($backends, $this['redisCache']);
                }
                if ($this['config']->get('cache.memcache.enable', false)) {
                    array_unshift($backends, $this['memcachedCache']);
                }
                return new MultipleCache($backends);
            }
        );

        // cookies
        $di->setShared(
            'cookies',
            function () {
                $cookies = new Cookies();
                $cookies->useEncryption(true);
                return $cookies;
            }
        );

        // modelsCache, 设置模型缓存服务, 默认使用fileCache，可以在使用中指定cacheService，使用前面定义的memCache or redisCache
        $di->set(
            "modelsCache",
            function () {
                $frontCache = new DataFrontend([
                    "lifetime" => $this['config']->get('app.model.cache.lifetime', 3600),
                ]);

                $cachePath = $this->tmpPath() . '/cache/models/';
                if (!file_exists($cachePath)) {
                    @mkdir($cachePath, 0755, true);
                }
                $cache = new FileCache(
                    $frontCache,
                    [
                        "cacheDir" => $cachePath
                    ]
                );
                return $cache;
            }
        );

        // modelsMetadata, 元数据管理
        $di->set(
            "modelsMetadata",
            function () {
                if ($this->environment() == 'production') {
                    $metaDataDir = $this->tmpPath() . '/cache/metadata';
                    if (!file_exists($metaDataDir)) {
                        @mkdir($metaDataDir, 0755, true);
                    }
                    return new FileMetaData([
                        'metaDataDir' => $metaDataDir
                    ]);
                }
                return new MemoryMetaData();
            }
        );

        // crypt
        $di->setShared(
            'crypt',
            function () {
                $crypt = new Crypt();
                $crypt->setKey($this['config']->get('app.key', '#1dj8$=dp?.ak//j1V$'));
                return $crypt;
            }
        );

        // dispatcher
        $di->setShared(
            'dispatcher',
            function () {
                $dispatcher = new Dispatcher();
                $dispatcher->setDefaultNamespace('App\\Http\\Controllers');
                $dispatcher->setModelBinding(true);

                // register event listener
                $this['eventsManager']->attach('dispatch', new \Pails\Plugins\CustomRender());

                return $dispatcher;
            }
        );

        // logger
        $di->setShared(
            'logger',
            function () {
                $date = date('Y-m-d');
                $logFile = $this->logPath() . DIRECTORY_SEPARATOR . $date . ".log";
                return new FileLogger($logFile);
            }
        );

        // errorLogger
        $di->setShared(
            'errorLogger',
            function () {
                $logFile = $this->logPath() . DIRECTORY_SEPARATOR . "error.log";
                return new FileLogger($logFile);
            }
        );

        // random
        $di->setShared(
            'random',
            Random::class
        );

        // session
        $di->setShared(
            'session',
            function () {
                $session = null;

                if ($this['config']->get('session.adapter', 'file') == 'file') {
                    $session = new FileSession($this['config']->get('session.options'));
                }

                if ($this['config']->get('session.adapter', 'file') == 'redis') {
                    $session = new RedisSession($this['config']->get('session.options'));
                }

                if ($this['config']->get('session.adapter', 'file') == 'memcached') {
                    $session = new MemcachedSession([
                        'servers' => [
                            [
                                'host' => $this['config']->get('session.options.host', 'localhost'),
                                'port' => $this['config']->get('session.options.port', 11211),
                                'weight' => 1,
                            ],
                        ],
                        'client' => [
                            \Memcached::OPT_HASH => \Memcached::HASH_MD5,
                            \Memcached::OPT_PREFIX_KEY => "prefix.",
                        ],
                        'lifetime' => $this['config']->get('session.options.lifetime', 3600),
                        'prefix' => $this['config']->get('session.options.prefix', '_session_'),
                        'uniqueId' => $this['config']->get('session.options.uniqueId', '_pails_app_'),
                    ]);
                }

                if (is_object($session) && !$session->isStarted()) {
                    $session->start();
                }

                return $session;
            }
        );

        // view
        $di->setShared(
            'view',
            function () {
                $view = new View();
                $view->setViewsDir($this->viewsPath());
                $view->registerEngines([
                    '.volt' => 'volt',
                    '.phtml' => 'Phalcon\Mvc\View\Engine\Php'
                ]);
                return $view;
            }
        );

        // viewCache, Not shared
        $di->set(
            "viewCache",
            function () {
                // Cache data for one day by default
                $frontCache = new OutputFrontend(
                    [
                        "lifetime" => $this['config']->get('app.view.cache.lifetime', 86400),
                    ]
                );

                $cachePath = $this->tmpPath() . '/cache/view/';
                if (!file_exists($cachePath)) {
                    @mkdir($cachePath, 0755, true);
                }
                $cache = new FileCache(
                    $frontCache,
                    [
                        "cacheDir" => $cachePath
                    ]
                );

                return $cache;
            }
        );

        // volt
        $di->setShared(
            'volt',
            function () {
                $compiledPath = $this->tmpPath() . '/cache/volt/';
                if (!file_exists($compiledPath)) {
                    @mkdir($compiledPath, 0755, true);
                }
                $volt = new Volt($this['view'], $this);
                $volt->setOptions([
                    'compiledPath' => $compiledPath,
                    'compiledSeparator' => '_',
                    'compileAlways' => $this['config']->get('app.debug', false)
                ]);

                $volt->getCompiler()->addExtension(new VoltExtension());

                return $volt;
            }
        );

        // url
        $di->setShared(
            'url',
            function () {
                $url = new Url();
                if ($baseUrl = $this['config']->get('url.base_url')) {
                    $url->setBaseUri($baseUrl);
                }
                if ($staticUrl = $this['config']->get('url.static_url')) {
                    $url->setStaticBaseUri($staticUrl);
                }
                $url->setBasePath($this->basePath());
                return $url;
            }
        );
    }
}
