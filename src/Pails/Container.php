<?php
namespace Pails;

use Pails\Exception\Handler;
use Pails\Providers;
use Pails\Providers\ServiceProviderInterface;
use Phalcon\Config;
use Phalcon\Di;
use Phalcon\Loader;
use Symfony\Component\Debug\Exception\FatalThrowableError;

/**
 * Class Application - 扩展Di,作为核心容器。类似laravel的 Application。
 * @package Pails
 */
class Container extends Di\FactoryDefault
{
    /**
     * Pails Version
     */
    const VERSION = '3.0.0';

    /**
     * @var string
     */
    protected $basePath;

    /**
     * Pails 框架一级的服务,在容器初始化的时候注册
     *
     * @var array
     */
    protected $providers = [
        Providers\ApiResponseServiceProvider::class,
        Providers\CollectionServiceProvider::class,
        Providers\DatabaseServiceProvider::class,
        Providers\DispatcherServiceProvider::class,
        Providers\FractalServiceProvider::class,
        Providers\InflectorServiceProvider::class,
        Providers\RouterServiceProvider::class,
        Providers\ViewServiceProvider::class,
        Providers\VoltServiceProvider::class
    ];

    /**
     * Application constructor.
     * @param null $basePath
     */
    public function __construct($basePath = null)
    {
        parent::__construct();

        if ($basePath) {
            $this->setBasePath($basePath);
        }

        $this->registerAutoLoader();

        $this->registerServices($this->providers);
    }

    /**
     * 注册服务列表
     * @param array $serviceProviders
     */
    public function registerServices($serviceProviders = [])
    {
        foreach ($serviceProviders as $serviceProviderClass) {
            $this->registerService(new $serviceProviderClass($this));
        }
        //throw new RuntimeException("aa");
    }

    /**
     * 注册一个服务
     * @param ServiceProviderInterface $serviceProvider
     * @return $this
     */
    public function registerService(ServiceProviderInterface $serviceProvider)
    {
        $serviceProvider->register();

        return $this;
    }

    /**
     * init
     */
    protected function registerAutoLoader()
    {
        $loader = new Loader();

        // \App base
        $loader->registerNamespaces([
            'App' => $this->appPath()
        ]);

        // Other
        $loader->registerDirs([
            $this->libPath()
        ]);

        $loader->register();

        return $this;
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
     * Helpers: Get the path to the storage directory.
     *
     * @return string
     */
    public function storagePath()
    {
        return $this->basePath . DIRECTORY_SEPARATOR . 'storage';
    }

    /**
     * Helpers: Get the path to the resource directory.
     *
     * @return string
     */
    public function resourcesPath()
    {
        return $this->basePath . DIRECTORY_SEPARATOR . 'resources';
    }

    /**
     * Helpers: Get the path to the views directory.
     *
     * @return string
     */
    public function viewsPath()
    {
        return $this->resourcesPath() . DIRECTORY_SEPARATOR . 'views';
    }

    /**
     * 获取配置. 自动注入到服务中
     *
     * @param $section
     * @param null $key
     * @param null $defaultValue
     * @return array|mixed
     */
    public function getConfig($section, $key = null, $defaultValue = null)
    {
        $serviceName = '___PAILS_CONFIG___' . $section;
        if ($this->has($serviceName)) {
            $service = $this->get($serviceName);
            if ($key) {
                return $service->get($key, $defaultValue);
            }
            return $service;
        }

        $configFile = $this->configPath() . DIRECTORY_SEPARATOR . $section . '.php';
        if (file_exists($configFile)) {
            // register a new config service
            $this->setShared($serviceName, Config::class);

            // instance the config service
            $config = require $configFile;
            $service = $this->get($serviceName, [$config]);
            if ($key) {
                return $service->get($key, $defaultValue);
            }
            return $service;
        } else {
            throw new \DomainException("config file ${section}.php not exists");
        }
    }

    /**
     * @param $appClass
     * @throws \Error
     * @throws \Exception
     * @throws \Phalcon\Exception
     */
    public function run($appClass)
    {
        try {
            $app = $this->getShared($appClass);
            $app->init()->boot()->handle()->send();
        } catch (\Exception $e) {
            $this->reportException($e);
            $this->renderException($e);
        } catch (\Throwable $e) {
            $this->reportException($e = new FatalThrowableError($e));
            $this->renderException($e);
        }
    }

    public function renderException(\Exception $e)
    {
        $this->getShared(Handler::class)->render($e);
    }

    public function reportException(\Exception $e)
    {
        $this->getShared(Handler::class)->report($e);
    }
}
