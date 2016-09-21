<?php
namespace Pails\Bootstraps;

use Pails\Container;
use Phalcon\Mvc\Dispatcher as PhalconDispatcher;
class Dispatcher
{
    public function boot(Container $container)
    {
        // As of phalcon 3, definition will bind to di by default
        $container->setShared('dispatcher', function() {
            $eventsManager = $this->getShared('eventsManager');
            $eventsManager->attach('dispatch', new \Pails\Plugins\CustomRender());

            $dispatcher = new PhalconDispatcher();
            $dispatcher->setEventsManager($eventsManager);
            $dispatcher->setDefaultNamespace('App\\Controllers');
            $dispatcher->setModelBinding(true);

            return $dispatcher;
        });
    }

}

