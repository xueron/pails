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
        $this->getDI()->registerServices($this->providers);

        // register services from services.php
        $services = (array)$this->getDI()->getConfig('services', null, []);
        foreach ($services as $name => $class) {
            $this->getDI()->setShared($name, $class);
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function init()
    {
        return $this;
    }
}
