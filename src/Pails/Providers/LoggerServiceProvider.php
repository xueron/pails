<?php
/**
 * LogServiceProvider.php
 *
 */


namespace Pails\Providers;


use Phalcon\Logger\Adapter\File;

class LoggerServiceProvider extends AbstractServiceProvider
{
    protected $serviceName = 'logger';

    function register()
    {
        $this->getDI()->setShared(
            $this->serviceName,
            function () {
                $date = date('Y-m-d');
                $logFile = $this->logPath() . DIRECTORY_SEPARATOR . $date . ".log";
                return new File($logFile);
            }
        );

    }
}
