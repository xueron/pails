<?php
namespace Pails\Support;

abstract class Bootstrap
{
    protected $app;

    public function __construct($app)
    {
        $this->app = $app;
    }

    abstract public function boot();
}
