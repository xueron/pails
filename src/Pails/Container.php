<?php
namespace Pails;

use Pails\Console\Application as ConsoleApplication;
use Pails\Exception\Handler;
use Pails\Providers\ServiceProviderInterface;
use Phalcon\Config;
use Phalcon\Di;
use Phalcon\Events\EventsAwareInterface;
use Phalcon\Http\Response;
use Phalcon\Loader;
use Phalcon\Version;
use Symfony\Component\Debug\Exception\FatalThrowableError;

/**
 * Class Application - 扩展Di,作为核心容器。类似laravel的 Application。
 *
 * @package Pails
 */
class Container extends Di\FactoryDefault implements ContainerInterface
{
    /**
     * Pails Version
     */
    const VERSION = '3.1.8';

    /**
     * @var Loader
     */
    protected $loader;

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
        Providers\PailsServiceProvider::class,
        Providers\CommonServiceProvider::class,
        Providers\DatabaseServiceProvider::class,
        Providers\RouterServiceProvider::class,
        Providers\OAuth2ServiceProvider::class,
    ];

    /**
     * Application constructor.
     *
     * @param null $basePath
     */
    public function __construct($basePath = null)
    {
        parent::__construct();

        if ($basePath) {
            $this->setBasePath($basePath);
        }

        if (!$this->getInternalEventsManager() && ($eventsManager = $this->getEventsManager())) {
            $this->setInternalEventsManager($eventsManager);
        }

        $this->registerAutoLoader();

        $this->registerServices($this->providers);
    }

    /**
     * 获取应用的环境
     *
     * @return string
     */
    public function environment()
    {
        $env = env('APP_ENV', 'development'); // May empty
        if (!$env) {
            $env = 'development';
        }

        return $env;
    }

    /**
     * 注册服务列表
     *
     * @param array $serviceProviders
     */
    public function registerServices($serviceProviders = [])
    {
        foreach ($serviceProviders as $serviceProviderClass) {
            $this->registerService(new $serviceProviderClass());
        }
    }

    /**
     * 注册一个服务
     *
     * @param ServiceProviderInterface $serviceProvider
     *
     * @return $this
     */
    public function registerService(ServiceProviderInterface $serviceProvider)
    {
        $serviceProvider->register();

        return $this;
    }

    /**
     * 注册loader。
     */
    protected function registerAutoLoader()
    {
        if (!$this->loader) {
            $this->loader = new Loader();
        }

        // Register App
        $this->loader->registerNamespaces([
            'App' => $this->appPath(),
        ])->register();

        return $this;
    }

    /**
     * Register namespaces. Append to exists.
     *
     * @param $namespaces
     *
     * @return $this
     */
    public function registerNamespaces($namespaces)
    {
        $this->loader->registerNamespaces($namespaces, true);

        return $this;
    }

    /**
     * Register namespace. Append to exists.
     *
     * @param $namespace
     * @param $path
     *
     * @return $this
     */
    public function registerNamespace($namespace, $path)
    {
        $this->loader->registerNamespaces([$namespace => $path], true);

        return $this;
    }

    /**
     * Get the version number of pails.
     *
     * @return string
     */
    public function version()
    {
        return static::VERSION;
    }

    /**
     * Get the version number of the phalcon framework.
     *
     * @return string
     */
    public function getPhalconVersion()
    {
        return Version::get();
    }

    /**
     * Set the base path for the application.
     *
     * @param string $basePath
     *
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
     *
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
     *
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
     * @param      $section
     * @param null $key
     * @param null $defaultValue
     *
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
            return $defaultValue;
        }
    }

    /**
     * @param $appClass
     *
     * @throws \Error
     * @throws \Exception
     * @throws \Phalcon\Exception
     */
    public function run($appClass)
    {
        try {
            $app = $this->getShared($appClass);

            $res = $app->init()->boot()->handle();

            // Mvc Application
            if ($res instanceof Response) {
                $res->send();
            } elseif (is_int($res) && $app instanceof ConsoleApplication) {
                exit($res);
            }
        } catch (\Exception $e) {
            $this->reportException($e);
            $this->renderException($e);
        } catch (\Throwable $e) {
            $this->reportException($e = new FatalThrowableError($e));
            $this->renderException($e);
        }
    }

    /**
     * @param \Exception $e
     */
    public function renderException(\Exception $e)
    {
        $this->getShared(Handler::class)->render($e);
    }

    /**
     * @param \Exception $e
     */
    public function reportException(\Exception $e)
    {
        $this->getShared(Handler::class)->report($e);
    }

    /**
     * Override DI's get() method, setEventsManager by default.
     *
     * {@inheritdoc}
     */
    public function get($name, $parameters = null)
    {
        $instance = parent::get($name, $parameters);

        if (is_object($instance) && $instance instanceof EventsAwareInterface) {
            if (!$instance->getEventsManager() && ($eventsManager = $this->getEventsManager())) {
                $instance->setEventsManager($eventsManager);
            }
        }

        return $instance;
    }
}
