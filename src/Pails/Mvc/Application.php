<?php
namespace Pails\Mvc;
use Pails\ApplicationInterface;

/**
 * Class Application
 * @package Pails\Mvc
 */
abstract class Application extends \Phalcon\Mvc\Application implements ApplicationInterface
{
    protected $providers = [];

    /**
     * register services
     * @return $this
     */
    public function boot()
    {
        $this->di->registerServices($this->providers);

        // register services from services.php
        $providers = (array)$this->di->getConfig('providers', null, []);
        $this->di->registerServices(array_values($providers));

        // register services from services.php
        $services = (array)$this->di->getConfig('services', null, []);
        foreach ($services as $name => $class) {
            $this->di->setShared($name, $class);
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function init()
    {
        //

        return $this;
    }
}
