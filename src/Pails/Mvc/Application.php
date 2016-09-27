<?php
namespace Pails\Mvc;
use Pails\Debug;

/**
 * Class Application
 * @package Pails\Mvc
 */
abstract class Application extends \Phalcon\Mvc\Application
{
    protected $debug = false;

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

    public function init()
    {
        if ($this->debug) {
            $debug = new Debug();
            //$debug->setUri('//pails.xueron.com/debug/3.0.x/');
            $debug->listen();
        }
        return $this;
    }
}
