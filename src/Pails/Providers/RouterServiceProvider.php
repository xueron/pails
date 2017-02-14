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
        //
        // 如果出现多级,比如Admin/Api,则一定要以Namespace的形式组织 Admin\Api\XxxxController
        //
        // Note: from phalcon 3, closure bind di as $this by default. so no use($app) needed.
        //
        $this->getDI()->setShared(
            $this->serviceName,
            function () {
                // 定义注解路由
                $router = new Annotations(false);
                $router->removeExtraSlashes(true);
                $router->setEventsManager($this->get('eventsManager'));
                $router->setDefaultNamespace('App\\Http\\Controllers');
                $router->setDefaultController('index');
                $router->setDefaultAction('index');

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

