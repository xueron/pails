<?php
namespace Pails\Mvc;

/**
 * Class Application
 * @package Pails\Mvc
 */
abstract class Application extends \Phalcon\Mvc\Application
{
    protected $providers = [];

    /**
     * register services
     */
    public function boot()
    {
        $this->getDI()->registerServices($this->providers);

        return $this;
    }

    public function init()
    {
        return $this;
    }
}
