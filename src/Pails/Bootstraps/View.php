<?php
namespace Pails\Bootstraps;
use Pails\Container;
use Phalcon\Mvc\View as PhalconView;
use Phalcon\Mvc\View\Engine\Volt;

class View
{
    /**
     * @param $container
     */
    public function boot(Container $container)
    {
        $container->setShared('view', function () {
            $view = new PhalconView();
            $view->setEventsManager($this->getShared('eventsManager'));
            $view->setViewsDir($this->path('views'));
            $view->registerEngines([
                '.volt' => 'volt',
                '.phtml' => 'Phalcon\Mvc\View\Engine\Php'
            ]);
            return $view;
        });

        $container->setShared('volt', function () {
            $volt = new Volt($this->get('view'));
            $volt->setOptions([
                'compiledPath' => $this->tmpPath() . '/cache/volt/',
                'compiledSeparator' => '_',
                'compileAlways' => true
            ]);
            $volt->getCompiler()->addExtension(new \Pails\Extensions\Volt());
            return $volt;
        });
    }
}

