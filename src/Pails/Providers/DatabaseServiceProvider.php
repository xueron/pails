<?php
namespace Pails\Providers;

use Phalcon\Db\Adapter\Pdo\Mysql;
use Phalcon\Logger\Adapter\File;
use Symfony\Component\Yaml\Yaml;

class DatabaseServiceProvider extends AbstractServiceProvider
{
    public function register()
    {
        $this->di->set(
            'db',
            function () {
                /* @var \Pails\Container $this */
                $env = $this->environment();
                $yaml = Yaml::parse(@file_get_contents($this->configPath() . '/database.yml'));
                if (!empty($yaml['environments'][$env])) {
                    $database = $yaml['environments'][$env];
                    $db = new Mysql([
                        'host'     => $database['host'],
                        'port'     => $database['port'],
                        'username' => $database['user'],
                        'password' => $database['pass'],
                        'dbname'   => $database['name'],
                        'charset'  => $database['charset'],
                    ]);
                    // debug sql
                    if ($this->get('config')->get('db.debug', false)) {
                        $logger = new File($this->logPath() . '/db_debug.log');
                        $this['eventsManager']->attach(
                            'db',
                            function ($event, $connection) use ($logger) {
                                if ($event->getType() == 'beforeQuery') {
                                    $sqlVariables = $connection->getSQLVariables();
                                    if (count($sqlVariables)) {
                                        $query = str_replace(['%', '?'], ['%%', "'%s'"], $connection->getSQLStatement());
                                        $query = vsprintf($query, $sqlVariables);
                                        $logger->log($query, \Phalcon\Logger::INFO);
                                    } else {
                                        $logger->log($connection->getSQLStatement(), \Phalcon\Logger::INFO);
                                    }
                                }
                            }
                        );
                    }

                    return $db;
                } else {
                    throw new \RuntimeException('no database config found. please check config file exists or APP_ENV is configed');
                }
            }
        );
        if ((true === $this->config->get('db.use_slave')) && $this->config->get('db.slave')) {
            $this->di->set(
                'dbRead',
                function () {
                    $dbRead = new Mysql([
                        'host'     => $this->config->get('db.slave.host'),
                        'port'     => $this->config->get('db.slave.port'),
                        'username' => $this->config->get('db.slave.user'),
                        'password' => $this->config->get('db.slave.pass'),
                        'dbname'   => $this->config->get('db.slave.name'),
                        'charset'  => $this->config->get('db.slave.charset'),
                    ]);

                    return $dbRead;
                }
            );
        }
    }
}
