<?php
namespace Pails\Bootstraps;

use Phalcon\Mvc\Router\Annotations;
use Phalcon\Text;

class Router
{
    public function boot($app)
    {
        $app->setShared('router', function () use ($app) {
            //
            $router = new Annotations(false);
            $router->setEventsManager($app->get('eventsManager'));
            $router->setDefaultNamespace('App\\Controllers\\');
            $router->setDefaultController('application');
            $router->setDefaultAction('index');

            //
            $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($app->path('Controllers')), \RecursiveIteratorIterator::SELF_FIRST);
            foreach ($iterator as $item) {
                if (Text::endsWith($item, "Controller.php", false)) {
                    $name = str_replace([$app->path('Controllers') . DIRECTORY_SEPARATOR, "Controller.php"], "", $item);
                    $name = str_replace(DIRECTORY_SEPARATOR, "\\", $name);
                    $router->addResource('App\\Controllers\\' . $name);
                }
            }

            //
            return $router;
        });
    }
}

