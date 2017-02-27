<?php
/**
 * PailsServiceProvider.php
 *
 */


namespace Pails\Providers;


use Pails\Plugins\ApiResponse;
use Pails\Plugins\Config;
use Pails\Plugins\Fractal;
use Pails\Plugins\VoltExtension;
use Pails\Pluralizer;
use Phalcon\Annotations\Adapter\Memory as MemoryAnnotations;
use Phalcon\Annotations\Adapter\Files as FileAnnotations;
use Phalcon\Cache\Backend\File as FileCache;
use Phalcon\Cache\Frontend\Data as DataFrontend;
use Phalcon\Cache\Frontend\Output as OutputFrontend;
use Phalcon\Crypt;
use Phalcon\Logger\Adapter\File;
use Phalcon\Mvc\Dispatcher;
use Phalcon\Mvc\Model\Metadata\Files as FileMetaData;
use Phalcon\Mvc\Model\MetaData\Memory as MemoryMetaData;
use Phalcon\Mvc\Url;
use Phalcon\Mvc\View;
use Phalcon\Mvc\View\Engine\Volt;
use Phalcon\Security\Random;
use Phalcon\Session\Adapter\Files;

class PailsServiceProvider extends AbstractServiceProvider
{
    function register()
    {
        $di = $this->getDI();

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

        // apiResponse
        $di->setShared(
            'apiResponse',
            ApiResponse::class
        );

        // cache
        $di->setShared('cache', function () {
            $frontCache = new DataFrontend([
                "lifetime" => $this->get('config')->get('cache.lifetime', 3600),
            ]);

            $cachePath = $this->tmpPath() . '/cache/data/';
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
        });

        // modelsCache, 设置模型缓存服务
        $di->set(
            "modelsCache",
            function () {
                $frontCache = new DataFrontend([
                    "lifetime" => $this->get('config')->get('cache.model.lifetime', 3600),
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

        // config
        $di->setShared(
            'config',
            Config::class
        );

        // crypt
        $di->setShared(
            'crypt',
            function () {
                $crypt = new Crypt();
                $crypt->setKey($this->config->get('app.key', '#1dj8$=dp?.ak//j1V$'));
                return $crypt;
            }
        );

        // dispatcher
        $di->setShared(
            'dispatcher',
            function () {
                $eventsManager = $this->getShared('eventsManager');
                $eventsManager->attach('dispatch', new \Pails\Plugins\CustomRender());

                $dispatcher = new Dispatcher();
                $dispatcher->setEventsManager($eventsManager);
                $dispatcher->setDefaultNamespace('App\\Http\\Controllers');
                $dispatcher->setModelBinding(true);

                return $dispatcher;
            }
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

        // logger
        $di->setShared(
            'logger',
            function () {
                $date = date('Y-m-d');
                $logFile = $this->logPath() . DIRECTORY_SEPARATOR . $date . ".log";
                return new File($logFile);
            }
        );

        // errorLogger
        $di->setShared(
            'errorLogger',
            function () {
                $logFile = $this->logPath() . DIRECTORY_SEPARATOR . "error.log";
                return new File($logFile);
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
                $session = new Files();
                if (!$session->isStarted()) {
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
                $view->setEventsManager($this->getShared('eventsManager'));
                $view->setViewsDir($this->viewsPath());
                $view->registerEngines([
                    '.volt' => 'volt',
                    '.phtml' => 'Phalcon\Mvc\View\Engine\Php'
                ]);
                return $view;
            }
        );

        // viewCache
        $di->set( // Not shared
            "viewCache",
            function () {
                // Cache data for one day by default
                $frontCache = new OutputFrontend(
                    [
                        "lifetime" => $this->get('config')->get('cache.view.lifetime', 86400),
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
                $volt = new Volt($this->get('view'), $this);
                $volt->setOptions([
                    'compiledPath' => $compiledPath,
                    'compiledSeparator' => '_',
                    'compileAlways' => $this->get('config')->get('app.debug', false)
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
                if ($baseUrl = $this->get('config')->get('url.base_url')) {
                    $url->setBaseUri($baseUrl);
                }
                return $url;
            }
        );
    }
}
