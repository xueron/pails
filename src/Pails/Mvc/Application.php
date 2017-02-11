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
