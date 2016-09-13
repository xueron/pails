<?php
namespace Pails\Bootstraps;

use Phalcon\Mvc\Router\Annotations;
use Phalcon\Text;

class Router
{
    public function boot($app)
    {
        // Pails 不使用Phalcon的Module功能,通过Namespace组织Controllers.
        // 如果出现多级,比如AdminApi,则一定要以Namespace的形式组织 Admin\Api\xxxxController
        //
        // Note: from phalcon 3, closure bind di as $this by default. so no use($app) needed.
        //
        $app->setShared('router', function () use ($app) {
            //
            $router = new Annotations(false);
            $router->removeExtraSlashes(true);
            $router->setEventsManager($app->get('eventsManager'));
            $router->setDefaultNamespace('App\\Controllers\\');
            $router->setDefaultController('application');
            $router->setDefaultAction('index');

            // Add a default route to:
            // /name/space/controller/action -> App\Controllers\Name\Space\Controller\Action
            $router->add(
                "/{namespace:[\w0-9\_\-\/]+}/:controller/:action",
                [
                    'controller' => 2,
                    'action'     => 3
                ]
            )->convert('namespace', function ($namespace) {
                $parts = explode("/", strtolower($namespace));
                return 'App\\Controllers\\' . implode("\\", array_map('ucfirst', $parts));
            });

            // Process /config/routes.php
            // Verb	        Path	            Action	Route Name
            // GET	        /photo	            index	photo.index
            // GET	        /photo/create	    create	photo.create
            // POST	        /photo	            store	photo.store
            // GET	        /photo/{photo}	    show	photo.show
            // GET	        /photo/{photo}/edit	edit	photo.edit
            // PUT/PATCH	/photo/{photo}	    update	photo.update
            // DELETE	    /photo/{photo}	    destroy	photo.destroy
            foreach ($app->getConfig('routes') as $url => $route) {
//                $resourceDefaults = ['index', 'create', 'store', 'show', 'edit', 'update', 'destroy'];
//
//                // a RESTful resource
//                if (isset($route['resource'])) {
//                    if (!isset($route[''])) {}
//                    foreach ($this->getResourceMethods($defaults, $options) as $m) {
//                        $this->{'addResource'.ucfirst($m)}($url, $route, $options);
//                    }
//                } else {
//                    if (count($route) !== count($route, COUNT_RECURSIVE)) {
//                        if (isset($route['pattern']) && isset($route['paths'])) {
//                            $method = isset($route['httpMethods']) ? $route['httpMethods'] : null;
//                            $router->add($route['pattern'], $route['paths'], $method);
//                        } else {
//                            throw new \RuntimeException(sprintf('No route pattern and paths found by route %s', $url));
//                        }
//                    } else {
//                        $router->add($url, $route);
//                    }
//                }
            }

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
            $router->notFound([
                "controller" => "application",
                "action"     => "notfound"
            ]);

            //
            return $router;
        });
    }

    protected function getResourceMethods($defaults, $options)
    {
        if (isset($options['only'])) {
            return array_intersect($defaults, (array) $options['only']);
        } elseif (isset($options['except'])) {
            return array_diff($defaults, (array) $options['except']);
        }

        return $defaults;
    }
}

