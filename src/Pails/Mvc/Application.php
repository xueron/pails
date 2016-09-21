<?php
namespace Pails\Mvc;

/**
 * Class Application
 * @package Pails\Mvc
 */
abstract class Application extends \Phalcon\Mvc\Application
{
    protected $bootstraps = [

    ];

    /**
     * register services
     */
    public function boot()
    {
        foreach ($this->bootstraps as $className) {
            $bootstrap = new $className();
            $bootstrap->boot($this->getDI());
        }

        return $this;
    }
}
