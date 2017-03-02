<?php
/**
 * ServiceHub.php
 *
 */
namespace Pails\Plugins;

use Pails\Injectable;

class ServiceHub extends Injectable
{
    public function service($service)
    {
        if (!is_object($service)) {
            $service = $this->di->getShared($service);
        }
        return $service;
    }
}
