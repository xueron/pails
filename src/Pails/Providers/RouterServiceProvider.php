<?php
namespace Pails\Providers;

use Phalcon\Mvc\Router\Annotations;
use Phalcon\Text;

class RouterServiceProvider extends AbstractServiceProvider
{
    protected $serviceName = 'router';

    public function register()
    {
        // Pails 不使用Phalcon的Module功能,通过Namespace组织Controllers.
        // 如果出现多级,比如AdminApi,则一定要以Namespace的形式组织 Admin\Api\xxxxController
        //
        // Note: from phalcon 3, closure bind di as $this by default. so no use($app) needed.
        //
        $this->getDI()->setShared(
            $this->serviceName,
            function () {
                //
                $router = new Annotations(false);
                $router->removeExtraSlashes(true);
                $router->setEventsManager($this->get('eventsManager'));
                $router->setDefaultNamespace('App\\Http\\Controllers');
                $router->setDefaultController('index');
                $router->setDefaultAction('index');

                // Process /config/routes.php
                // Verb	        Path	            Action	Route Name
                // GET	        /photo	            index	photo.index
                // GET	        /photo/create	    create	photo.create
                // POST	        /photo	            store	photo.store
                // GET	        /photo/{photo}	    show	photo.show
                // GET	        /photo/{photo}/edit	edit	photo.edit
                // PUT/PATCH	/photo/{photo}	    update	photo.update
                // DELETE	    /photo/{photo}	    destroy	photo.destroy
//                foreach ($this->getConfig('routes') as $url => $route) {
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
//                }

                // 定义注解路由
                $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($this->path('Http/Controllers')), \RecursiveIteratorIterator::SELF_FIRST);
                foreach ($iterator as $item) {
                    if (Text::endsWith($item, "Controller.php", false)) {
                        $name = str_replace([$this->path('Http/Controllers') . DIRECTORY_SEPARATOR, "Controller.php"], "", $item);
                        $name = str_replace(DIRECTORY_SEPARATOR, "\\", $name);
                        $router->addResource('App\\Http\\Controllers\\' . $name);
                    }
                }

                // 定义404路由
                $router->notFound([
                    "controller" => "index",
                    "action" => "notfound"
                ]);

                //
                return $router;
            }
        );
    }
}

