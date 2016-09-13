<?php
namespace Pails\Bootstraps;
use Phalcon\Mvc\View as PhalconView;
use Phalcon\Mvc\View\Engine\Volt;

class View
{
    /**
     * @param $app
     */
    public function boot($app)
    {
        $app->setShared('view', function () use ($app) {
            $view = new PhalconView();
            $view->setEventsManager($app->getShared('eventsManager'));
            $view->setViewsDir($app->path('views'));
            $view->registerEngines([
                '.volt' => 'volt',
                '.phtml' => 'Phalcon\Mvc\View\Engine\Php'
            ]);
            return $view;
        });

        $app->setShared('volt', function () use ($app) {
            $volt = new Volt($app->get('view'));
            $volt->setOptions([
                'compiledPath' => $app->tmpPath() . '/cache/volt/',
                'compiledSeparator' => '_',
                'compileAlways' => true
            ]);
            $volt->getCompiler()->addExtension(new \Pails\Extensions\Volt());
            return $volt;
        });
    }
}

