<?php
/**
 * Application.php
 *
 */

namespace Pails\Listeners;

use Pails\Injectable;

class Application extends Injectable
{
    public function boot($event, $application)
    {
    }

    public function beforeStartModule($event, $application, $moduleName)
    {
    }

    public function afterStartModule($event, $application, $module)
    {
    }

    public function beforeHandleRequest($event, $application, $dispatcher)
    {
    }

    public function afterHandleRequest($event, $application, $controller)
    {
    }

    public function viewRender($event, $application, $view)
    {
    }

    public function beforeSendResponse($event, $application, $response)
    {
    }
}
