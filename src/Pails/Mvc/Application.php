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
    protected function boot()
    {
        foreach ($this->bootstrappers as $name => $className) {
            $bootstrap = new $className;
            $bootstrap->boot($this->di);
        }

        return $this;
    }
}
