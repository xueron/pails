<?php
namespace Pails;

use Phalcon\DiInterface;
use Phalcon\Events\ManagerInterface;

/**
 * Class InjectableTrait
 * @package Pails
 */
trait InjectableTrait
{
    /**
     * @var DiInterface
     */
    protected $_dependencyInjector;

    /**
     * @var ManagerInterface
     */
    protected $_eventsManager;

    /**
     * @param DiInterface $container
     */
    public function setDI(DiInterface $container)
    {
        $this->_dependencyInjector = $container;
    }

    /**
     * @return DiInterface
     */
    public function getDI()
    {
        if (!is_object($this->_dependencyInjector)) {
            $this->_dependencyInjector = Container::getDefault();
        }
        return $this->_dependencyInjector;
    }

    /**
     * @param ManagerInterface $eventsManager
     */
    public function setEventsManager(ManagerInterface $eventsManager)
    {
        $this->_eventsManager = $eventsManager;
    }

    /**
     * @return ManagerInterface
     */
    public function getEventsManager()
    {
        if (!is_object($this->_eventsManager)) {
            $this->_eventsManager = $this->getDI()->getEventsManager();
        }
        return $this->_eventsManager;
    }

    /**
     * Magic method __get
     * @param $name
     * @return mixed
     */
    public function __get($name)
    {
        $di = $this->_dependencyInjector;
        if (!is_object($di)) {
            $di = Container::getDefault();
            if (!is_object($di)) {
                throw new \RuntimeException("A dependency injection object is required to access the application services");
            }
        }

        if ($di->has($name)) {
            $service = $di->getShared($name);
            $this->$name = $service;
            return $service;
        }

        if ($name == 'di') {
            $this->di = $di;
            return $di;
        }

        if ($name == 'persistent') {
            $persistent = $di->get('sessionBag', [get_class($this)]);
            $this->persistent = $persistent;
            return $persistent;
        }

        trigger_error("Access to undefined property $name");
        return null;
    }
}
