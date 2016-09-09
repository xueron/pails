<?php
namespace Pails;

use Phalcon\Di;
use Phalcon\Loader;
use Phalcon\Mvc\Router\Annotations;
use Phalcon\Text;

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
    protected $base_services = [
        "router" => "Phalcon\\Mvc\\Router",
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
    protected $core_services = [
        'inflector' => 'Pails\\Util\\Inflector',
    ];

    /**
     * 应用的默认服务,在应用里面重载。
     * @var array
     */
    protected $default_services = [

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

        $this->registerDefaultServices();

        if ($basePath) {
            $this->setBasePath($basePath);
        }
    }

    /**
     * register Container self as a service
     */
    protected function registerBaseServices()
    {
        foreach ($this->base_services as $name => $className) {
            $this->_services[$name] = new Di\Service($name, $className, true);
        }

        // 注册自身
        $this->setShared('app', $this);
        $this->setShared('di', $this);
    }

    /**
     * register Phalcon's build-in services
     */
    protected function registerDefaultServices()
    {
        foreach ($this->default_services as $name => $className) {
            $this->_services[$name] = new Di\Service($name, $className, true);
        }
    }

    /**
     * register Pails' build-in services
     */
    protected function registerCoreServices()
    {
        foreach ($this->core_services as $name => $className) {
            $this->_services[$name] = new Di\Service($name, $className, true);
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
     * @return string
     */
    public function basePath()
    {
        return $this->basePath;
    }

    /**
     * Get the path to the bootstrap directory.
     *
     * @return string
     */
    public function bootstrapPath()
    {
        return $this->basePath . DIRECTORY_SEPARATOR . 'bootstrap';
    }

    /**
     * Get the path to the application configuration files.
     *
     * @return string
     */
    public function configPath()
    {
        return $this->basePath . DIRECTORY_SEPARATOR . 'config';
    }

    /**
     * Get the path to the database directory.
     *
     * @return string
     */
    public function databasePath()
    {
        return $this->databasePath ?: $this->basePath . DIRECTORY_SEPARATOR . 'database';
    }


    private function initLoader()
    {
        $loader = new Loader();

        // \App base
        $loader->registerNamespaces([
            'App' => $this->path()
        ]);

        // Other
        $loader->registerDirs([
            $this->basePath() . '/lib'
        ]);

        $loader->register();
    }

    private function initRouter()
    {
        $this->setShared('router', function () {
            //
            $router = new Annotations(false);
            $router->setEventsManager($this->get('eventsManager'));
            $router->setDefaultNamespace('App\\Controllers\\');
            $router->setDefaultController('application');
            $router->setDefaultAction('index');

            //
            $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($this->path('Controllers')), \RecursiveIteratorIterator::SELF_FIRST);
            foreach ($iterator as $item) {
                if (Text::endsWith($item, "Controller.php", false)) {
                    $name = str_replace([$this->path('Controllers'), "Controller.php"], "", $item);
                    $name = str_replace("/", "\\", $name);
                    $router->addResource('App\\Controller\\' . $name);
                }
            }

            //
            return $router;
        });
    }

    private function _initConfig()
    {

    }

    private function _initView()
    {
        $this->di->setShared('view', function () {
            $view = new View();
            $view->setViewsDir($this->getViewsDir());
            $view->registerEngines([
                '.volt' => 'volt',
                '.phtml' => 'Phalcon\Mvc\View\Engine\Php'
            ]);
            return $view;
        });
    }

    private function _initVolt()
    {
        $this->setShared('volt', function () {
            $volt = new Volt($this->get('view'));
            $volt->setOptions([
                'compiledPath' => dirname($this->app_root) . '/tmp/cache/volt/',
                'compiledSeparator' => '_',
                'compileAlways' => $this->debug
            ]);
            $volt->getCompiler()->addExtension(new \Pails\Extension\Volt());
            return $volt;
        });
    }

    private function _initDatabase()
    {
        $this->setShared('db', function () {
            return new \Phalcon\Db\Adapter\Pdo\Mysql(array(
                "host" => 'localhost',
                "username" => 'root',
                "password" => '',
                "dbname" => 'dowedo-account',
                'charset' => 'utf8',
            ));
        });
    }

    public function run()
    {
        $app = new \Phalcon\Mvc\Application($this);

        $app->handle()->send();
    }
} // End Application
