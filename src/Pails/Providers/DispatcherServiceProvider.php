<?php
namespace Pails\Providers;

use Phalcon\Mvc\Dispatcher as PhalconDispatcher;

class DispatcherServiceProvider extends AbstractServiceProvider
{
    protected $serviceName = 'dispatcher';

    public function register()
    {
        // As of phalcon 3, definition will bind to di by default
        $this->getDI()->setShared(
            $this->serviceName,
            function () {
                $eventsManager = $this->getShared('eventsManager');
                $eventsManager->attach('dispatch', new \Pails\Plugins\CustomRender());

                $dispatcher = new PhalconDispatcher();
                $dispatcher->setEventsManager($eventsManager);
                $dispatcher->setDefaultNamespace('App\\Controllers');
                $dispatcher->setModelBinding(true);

                return $dispatcher;
            }
        );
    }

}

