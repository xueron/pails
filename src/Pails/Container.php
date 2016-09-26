<?php
namespace Pails;

use Pails\Bootstraps;
use Phalcon\Di;
use Phalcon\Loader;

/**
 * Class Application - 扩展Di,作为核心容器。类似laravel的 Application。
 * @package Pails
 */
class Container extends Di\FactoryDefault
{
    /**
     * Pails Version
     */
    const VERSION = '0.0.1';

    /**
     * @var string
     */
    protected $basePath;

    /**
     * Pails的关键服务
     * @var array
     */
    protected $coreServices = [
        'inflector' => \Pails\Utils\Inflector::class,
    ];

    /**
     * 通过Bootstraps重新定义默认的设置
     *
     * @var array
     */
    protected $bootstraps = [
        Bootstraps\Router::class,
        Bootstraps\View::class,
        Bootstraps\Dispatcher::class,
    ];

    /**
     * Application constructor.
     * @param null $basePath
     */
    public function __construct($basePath = null)
    {
        // INIT Phalcon's DI
        parent::__construct();

        //
        $this->registerBaseServices();

        //
        $this->registerCoreServices();

        if ($basePath) {
            $this->setBasePath($basePath);
        }

        $this->boot();

        $this->init();
    }

    /**
     * register Container self as a service
     */
    protected function registerBaseServices()
    {
        // No need to register self again. 'di' is automatically injected for Injectable services
        $this->setShared('di', $this);
    }

    /**
     * register Pails' build-in services
     */
    protected function registerCoreServices()
    {
        foreach ($this->coreServices as $name => $className) {
            $this->_services[$name] = new Di\Service($name, $className, true);
        }
    }

    /**
     * register Phalcon's build-in services
     */
    public function boot()
    {
        foreach ($this->bootstraps as $className) {
            $bootstrap = new $className();
            $bootstrap->boot($this);
        }
    }

    /**
     * init
     */
    protected function init()
    {
        $loader = new Loader();

        // \App base
        $loader->registerNamespaces([
            'App' => $this->path() . '/'
        ]);

        // Other
        $loader->registerDirs([
            $this->libPath()
        ]);

        $loader->register();
    }

    /**
     * Get the version number of the application.
     *
     * @return string
     */
    public function version()
    {
        return static::VERSION;
    }

    /**
     * Set the base path for the application.
     *
     * @param  string $basePath
     * @return $this
     */
    public function setBasePath($basePath)
    {
        $this->basePath = rtrim($basePath, '\/');

        return $this;
    }

    /**
     * Get the path to the application "app" directory.
     *
     * @param string $dir
     * @return string
     */
    public function path($dir = '')
    {
        $path = $this->basePath . DIRECTORY_SEPARATOR . 'app';
        return $dir == '' ? $path : $path . DIRECTORY_SEPARATOR . rtrim($dir, '\/');
    }

    /**
     * Get the base path of the current Pails application.
     *
     * @param string $dir
     * @return string
     */
    public function basePath($dir = '')
    {
        return $dir == '' ? $this->basePath : $this->basePath . DIRECTORY_SEPARATOR . rtrim($dir, '\/');
    }

    /**
     * Helpers: Get the path to the application directory.
     *
     * @return string
     */
    public function appPath()
    {
        return $this->basePath . DIRECTORY_SEPARATOR . 'app';
    }

    /**
     * Helpers: Get the path to the application configuration files.
     *
     * @return string
     */
    public function configPath()
    {
        return $this->basePath . DIRECTORY_SEPARATOR . 'config';
    }

    /**
     * Helpers: Get the path to the database directory.
     *
     * @return string
     */
    public function databasePath()
    {
        return $this->basePath . DIRECTORY_SEPARATOR . 'db';
    }

    /**
     * Helpers: Get the path to the library directory.
     *
     * @return string
     */
    public function libPath()
    {
        return $this->basePath . DIRECTORY_SEPARATOR . 'lib';
    }

    /**
     * Helpers: Get the path to the log directory.
     *
     * @return string
     */
    public function logPath()
    {
        return $this->basePath . DIRECTORY_SEPARATOR . 'log';
    }

    /**
     * Helpers: Get the path to the tmp directory.
     *
     * @return string
     */
    public function tmpPath()
    {
        return $this->basePath . DIRECTORY_SEPARATOR . 'tmp';
    }

    /**
     * 获取配置. 自动注入到服务中
     *
     * @param $name
     * @param null $key
     * @param null $defaultValue
     * @return array|mixed
     */
    public function getConfig($name, $key = null, $defaultValue = null)
    {
        $serviceName = '___PAILS_CONFIG___' . $name;
        if ($this->has($serviceName)) {
            $service = $this->get($serviceName);
            if ($key) {
                return $service->get($key, $defaultValue);
            }
            return $service;
        }

        $configFile = $this->configPath() . DIRECTORY_SEPARATOR . $name . '.php';
        if (file_exists($configFile)) {
            $this->setShared($serviceName, "Phalcon\\Config");

            $config = require $configFile;
            $service = $this->get($serviceName, [$config]);
            if ($key) {
                return $service->get($key);
            }
            return $service;
        }

        return null;
    }

    /**
     * @param $appClass
     * @throws \Exception
     * @throws \Phalcon\Mvc\Dispatcher\Exception
     */
    public function run($appClass)
    {
        $app = new $appClass($this);

        try {
            $app->boot()->handle()->send();
        } catch (\Phalcon\Mvc\Dispatcher\Exception $e) {
            echo "Dispatcher Error";
            throw $e;
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
