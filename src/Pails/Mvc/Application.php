<?php
namespace Pails\Mvc;

/**
 * Class Application
 * @package Pails\Mvc
 */
class Application extends \Phalcon\Mvc\Application
{
    protected $bootstraps = [

    ];

    /**
     * register Phalcon's build-in services
     */
    public function boot()
    {
        foreach ($this->bootstraps as $name => $className) {
            $bootstrap = new $className();
            $bootstrap->boot($this->di);
        }

        return $this;
    }
}
