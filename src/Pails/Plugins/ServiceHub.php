<?php
/**
 * ServiceHub.php
 *
 */
namespace Pails\Plugins;

use Phalcon\Mvc\User\Plugin;

class ServiceHub extends Plugin
{
    public function service($service)
    {
        if (!is_object($service)) {
            $service = $this->getDI()->getShared($service);
        }


    }
}
