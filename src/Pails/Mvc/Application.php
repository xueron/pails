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

        // register services from providers.php
        $providers = (array)$this->di->getConfig('providers', null, []);
        $this->di->registerServices(array_values($providers));

        // register services from services.php
        $services = (array)$this->di->getConfig('services', null, []);
        foreach ($services as $name => $class) {
            $this->di->setShared($name, $class);
        }

        // register listeners from listeners.php
        $listeners = (array)$this->di->getConfig('listeners', null, []);
        foreach ($listeners as $event => $listener) {
            $this->eventsManager->attach($event, $this->di->getShared($listener));
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
