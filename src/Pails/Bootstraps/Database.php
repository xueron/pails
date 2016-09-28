<?php
namespace Pails\Bootstraps;


use Pails\Container;
use Symfony\Component\Yaml\Yaml;
use Phalcon\Db\Adapter\Pdo\Mysql;

class Database
{
    public function boot(Container $container)
    {
        $container->setShared('db', function () {
            $env = env('APP_ENV', 'development');
            $yaml = Yaml::parse(@file_get_contents($this->configPath() . '/database.yml'));
            if (!empty($yaml['environments'][$env])) {
                $database = $yaml['environments'][$env];
                return new Mysql(array(
                    "host" => $database['host'],
                    "port" => $database['port'],
                    "username" => $database['user'],
                    "password" => $database['pass'],
                    "dbname" => $database['name'],
                    'charset' => $database['charset'],
                ));
            }
        });
    }
}

