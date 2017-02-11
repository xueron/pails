<?php
/**
 * ListCommand.php
 *
 */


namespace Pails\Console\Commands\Route;


use Pails\Console\Command;
use Phalcon\Text;

class ListCommand extends Command
{
    protected $name = "route:list";

    protected $description = "列出已经定义的路由";

    public function handle()
    {
        $router = $this->getDI()->getRouter();
        $router->handle('/');

        $routes = $router->getRoutes();
        $data = [];
        foreach ($routes as $route) {
            $row['domain'] = $route->getHostname();
            $row['methods'] = $route->getHttpMethods();
            $row['pattern'] = $route->getPattern();
            $row['name'] = $route->getName();
            $paths = $route->getPaths();
            $row['controller'] = $paths['namespace'] . '\\' . Text::camelize($paths['controller']) . 'Controller';
            $row['action'] = $paths['action'] . 'Action';
            $row['group'] = $route->getGroup();
            $data[] = $row;
        }

        $headers = ['Domain', 'Method', 'URI', 'Name', 'Controller', 'Action', 'Group'];
        $this->table($headers, $data);
    }
}
