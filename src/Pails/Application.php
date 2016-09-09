<?php
namespace Pails;

use Phalcon\Di;
use Phalcon\Loader;

/**
 * Class Application - 扩展Di,作为核心容器。类似laravel的 Application。
 * @package Pails
 */
class Application extends Di
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
     * Phalcon默认的服务
     * @var array
     */
    protected $baseServices = [
        "router" => \Phalcon\Mvc\Router::class,  //"Phalcon\\Mvc\\Router",
        "dispatcher" => "dispatcher", "Phalcon\\Mvc\\Dispatcher",
        "url" => "Phalcon\\Mvc\\Url",
        "modelsManager" => "Phalcon\\Mvc\\Model\\Manager",
        "modelsMetadata" => "Phalcon\\Mvc\\Model\\MetaData\\Memory",
        "response" => "Phalcon\\Http\\Response",
        "cookies" => "Phalcon\\Http\\Response\\Cookies",
        "request" => "Phalcon\\Http\\Request",
        "filter" => "Phalcon\\Filter",
        "escaper" => "Phalcon\\Escaper",
        "security" => "Phalcon\\Security",
        "crypt" => "Phalcon\\Crypt",
        "annotations" => "Phalcon\\Annotations\\Adapter\\Memory",
        "flash" => "Phalcon\\Flash\\Direct",
        "flashSession" => "Phalcon\\Flash\\Session",
        "tag" => "Phalcon\\Tag",
        "session" => "Phalcon\\Session\\Adapter\\Files",
        "sessionBag" => "Phalcon\\Session\\Bag",
        "eventsManager" => "Phalcon\\Events\\Manager",
        "transactionManager" => "Phalcon\\Mvc\\Model\\Transaction\\Manager",
        "assets" => "Phalcon\\Assets\\Manager"
    ];

    /**
     * Pails的关键服务
     * @var array
     */
    protected $coreServices = [
        'inflector' => 'Pails\\Util\\Inflector',
    ];

    /**
     * 应用的默认服务,在应用里面重载。
     * @var array
     */
    protected $bootstraps = [

    ];

    /**
     * Application constructor.
     * @param null $basePath
     */
    public function __construct($basePath = null)
    {
        // INIT DI
        parent::__construct();

        $this->registerBaseServices();

        $this->registerCoreServices();

        if ($basePath) {
            $this->setBasePath($basePath);
        }

        // 初始化
        $this->init();

        // 引导(执行应用内的初始化)
        $this->boot();
    }

    /**
     * register Container self as a service
     */
    protected function registerBaseServices()
    {
        foreach ($this->baseServices as $name => $className) {
            $this->_services[$name] = new Di\Service($name, $className, true);
        }

        // 注册自身
        $this->setShared('app', $this);
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
     * init
     */
    protected function init()
    {
        $loader = new Loader();

        // \App base
        $loader->registerNamespaces([
            'App' => $this->path()
        ]);

        // Other
        $loader->registerDirs([
            $this->libPath()
        ]);

        $loader->register();
    }

    /**
     * register Phalcon's build-in services
     */
    protected function boot()
    {
        foreach ($this->bootstraps as $name => $className) {
            $bootstrap = new $className;
            $bootstrap->boot($this);
        }
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
    public function tpmPath()
    {
        return $this->basePath . DIRECTORY_SEPARATOR . 'tmp';
    }

    public function run()
    {
        $app = new \Phalcon\Mvc\Application($this);

        $app->handle()->send();
    }
} // End Application
