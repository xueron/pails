<?php
/**
 * view.php
 *
 */

namespace Pails\Listeners;

use Pails\Injectable;

class View extends Injectable
{
    public function beforeRender($event, $view)
    {
    }

    public function afterRender($event, $view)
    {
    }

    public function beforeRenderView($event, $view, $viewEnginePath)
    {
    }

    public function afterRenderView($event, $view)
    {
    }

    public function notFoundView($event, $view, $viewEnginePath)
    {
    }
}
