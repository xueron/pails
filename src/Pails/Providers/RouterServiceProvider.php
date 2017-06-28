<?php

namespace Pails\Providers;

use Phalcon\Mvc\Router;
use Phalcon\Mvc\Router\Annotations;
use Phalcon\Text;

class RouterServiceProvider extends AbstractServiceProvider
{
    protected $serviceName = 'router';

    public function register()
    {
        // Pails 不使用Phalcon的Module功能,通过Namespace组织Controllers.
        //
        // 如果出现多级,比如Admin/Api,则一定要以Namespace的形式组织 Admin\Api\XxxxController
        //
        // Note: from phalcon 3, closure bind di as $this by default. so no use($app) needed.
        //
        $this->di->setShared(
            $this->serviceName,
            function () {
                /* @var \Pails\Container $this */
                /* @var \Phalcon\Mvc\Router $router */
                $routesFile = $this->tmpPath() . '/cache/routes/cache.php';
                if ($this['config']->get('router.use_cache') && file_exists($routesFile)) {
                    $router = $this->get(Router::class, [false]);
                    $routes = require $routesFile;
                    foreach ($routes as $route) {
                        $router->add($route['pattern'], $route['paths'], $route['method']);
                    }
                } else {
                    // 定义注解路由
                    $router = $this->get(Annotations::class, [false]);

                    $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($this->path('Http/Controllers')), \RecursiveIteratorIterator::SELF_FIRST);
                    foreach ($iterator as $item) {
                        if (Text::endsWith($item, 'Controller.php', false)) {
                            $name = str_replace([$this->path('Http/Controllers') . DIRECTORY_SEPARATOR, 'Controller.php'], '', $item);
                            $name = str_replace(DIRECTORY_SEPARATOR, '\\', $name);
                            $router->addResource('App\\Http\\Controllers\\' . $name);
                        }
                    }
                }

                // 手工定义的route
                $routes = $this['config']->get('router.routes');
                foreach ($routes as $route) {
                    $router->add($route['pattern'], $route['paths'], $route['method']);
                }

                // 基本设置
                $router->removeExtraSlashes(true);
                $router->setDefaultNamespace('App\\Http\\Controllers');
                $router->setDefaultController('index');
                $router->setDefaultAction('index');

                // 定义404路由
                $router->notFound($this['config']->get('router.not_found', [
                    'controller' => 'index',
                    'action'     => 'notfound',
                ]));

                // 通过事件的方式，在handle后缓存
                if ($this['config']->get('router.use_cache')) {
                    $router->getEventsManager()->attach('router:beforeCheckRoutes', function ($event, $router) use ($routesFile) {
                        // dump cache
                        if (!file_exists($routesFile)) {
                            $routes = $router->getRoutes();
                            $arrayR = [];
                            foreach ($routes as $route) {
                                $arrayR[] = [
                                    'pattern' => $route->getPattern(),
                                    'paths'   => $route->getPaths(),
                                    'method'  => $route->getHttpMethods(),
                                ];
                            }
                            @file_put_contents($routesFile, '<?php return ' . var_export($arrayR, true) . ';');
                        }
                    });
                }

                //
                return $router;
            }
        );
    }
}
