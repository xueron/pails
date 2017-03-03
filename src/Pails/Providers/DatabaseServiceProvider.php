<?php
namespace Pails\Providers;

use Phalcon\Logger\Adapter\File;
use Symfony\Component\Yaml\Yaml;
use Phalcon\Db\Adapter\Pdo\Mysql;

class DatabaseServiceProvider extends AbstractServiceProvider
{
    public function register()
    {
        $this->di->set(
            'db',
            function () {
                $env = env('APP_ENV', 'development');
                $yaml = Yaml::parse(@file_get_contents($this->configPath() . '/database.yml'));
                if (!empty($yaml['environments'][$env])) {
                    $database = $yaml['environments'][$env];
                    $db = new Mysql(array(
                        "host" => $database['host'],
                        "port" => $database['port'],
                        "username" => $database['user'],
                        "password" => $database['pass'],
                        "dbname" => $database['name'],
                        'charset' => $database['charset'],
                    ));

                    // debug sql
                    if ($this->get('config')->get('app.debug', false)) {
                        $eventsManager = $this->getEventsManager();
                        $logger = new File($this->logPath() . '/db_query.log');

                        $eventsManager->attach(
                            'db',
                            function ($event, $connection) use ($logger) {

                                if ($event->getType() == 'beforeQuery') {
                                    $sqlVariables = $connection->getSQLVariables();
                                    if (count($sqlVariables)) {
                                        $query = str_replace(array('%', '?'), array('%%', "'%s'"), $connection->getSQLStatement());
                                        $query = vsprintf($query, $sqlVariables);

                                        $logger->log($query, \Phalcon\Logger::INFO);
                                    } else {
                                        $logger->log($connection->getSQLStatement(), \Phalcon\Logger::INFO);
                                    }
                                }
                            }
                        );
                        $db->setEventsManager($eventsManager);
                    }

                    return $db;
                } else {
                    throw new \RuntimeException("no database config found. please check config file exists or APP_ENV is configed");
                }
            }
        );
    }
}
