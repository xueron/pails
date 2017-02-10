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
use Phalcon\Cache\Frontend\Data;
use Phalcon\Crypt;
use Phalcon\Logger\Adapter\File;
use Phalcon\Mvc\Dispatcher;
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
            $frontCache = new Data([
                "lifetime" => $this->config->get('cache.lifetime', 3600),
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
                    'compileAlways' => @constant('APP_DEBUG') ?: false
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
                if ($baseUrl = $this->config->get('url.base_url')) {
                    $url->setBaseUri($baseUrl);
                }
                return $url;
            }
        );
    }
}
