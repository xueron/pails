<?php
namespace Pails\Bootstraps;

use Phalcon\Mvc\Dispatcher as PhalconDispatcher;
class Dispatcher
{
    public function boot($app)
    {
        $app->setShared('dispatcher', function() use ($app) {
            $eventsManager = $app->getShared('eventsManager');
            $eventsManager->attach('dispatch', new \Pails\Plugins\CustomRender());

            $dispatcher = new PhalconDispatcher();
            $dispatcher->setEventsManager($eventsManager);
            $dispatcher->setDefaultNamespace('App\\Controllers\\');
            return $dispatcher;
        });
    }

}

