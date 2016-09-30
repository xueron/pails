<?php
namespace Pails\Plugins;

use Phalcon\Events\EventInterface;
use Phalcon\Mvc\DispatcherInterface;
use Phalcon\Mvc\User\Plugin;

class CustomRender extends Plugin
{
    public function beforeExecuteRoute(EventInterface $event, DispatcherInterface $dispatcher)
    {
        $defaultNamespace = $dispatcher->getDefaultNamespace();
        $namespace = $dispatcher->getNamespaceName();

        if (strpos($namespace, $defaultNamespace) === 0) {
            $namespace = substr($namespace, strlen($defaultNamespace));
        }

        $parts   = array_filter(explode('\\', strtolower($namespace)));
        $parts[] = $dispatcher->getControllerName();
        $parts[] = $dispatcher->getActionName();

        $this->view->pick(implode(DIRECTORY_SEPARATOR, $parts));
    }
}
