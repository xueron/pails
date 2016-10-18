<?php
namespace Pails\Providers;

use Phalcon\Mvc\View as PhalconView;

class ViewServiceProvider extends AbstractServiceProvider
{
    protected $serviceName = 'view';

    public function register()
    {
        $this->getDI()->setShared(
            $this->serviceName,
            function () {
                // Closure, $this is maped to DI
                $view = new PhalconView();
                $view->setEventsManager($this->getShared('eventsManager'));
                $view->setViewsDir($this->viewsPath());
                $view->registerEngines([
                    '.volt' => 'volt',
                    '.phtml' => 'Phalcon\Mvc\View\Engine\Php'
                ]);
                return $view;
            }
        );
    }
}

