<?php
namespace Pails\Mvc;

use Pails\ApplicationInterface;

/**
 * Class Application
 *
 * @package Pails\Mvc
 */
abstract class Application extends \Phalcon\Mvc\Application implements ApplicationInterface
{
    protected $providers = [];

    /**
     * register services
     *
     * @return $this
     */
    public function boot()
    {
        $this->di->registerServices($this->providers);

        // register services from providers.php
        $providers = (array) $this->di->getConfig('providers', null, []);
        $this->di->registerServices(array_values($providers));

        // register services from services.php
        $services = (array) $this->di->getConfig('services', null, []);
        foreach ($services as $name => $class) {
            $this->di->setShared($name, $class);
        }

        // register listeners from listeners.php
        $listeners = $this->di->getConfig('listeners', null);
        foreach ($listeners as $eventId => $listener) {
            if (is_string($listener)) {
                $this->eventsManager->attach($eventId, $this->di->getShared($listener));
            } else {
                $event = $listener['event'];
                $class = $listener['class'];
                $pri   = $listener['pri'];
                $this->eventsManager->attach($event, $this->di->getShared($class), $pri);
            }
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
