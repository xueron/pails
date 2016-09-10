<?php
namespace Pails\Plugins;

use Phalcon\Mvc\DispatcherInterface;

class CustomRender extends \Phalcon\Mvc\User\Plugin
{
    public function beforeExecuteRoute($event, DispatcherInterface $dispatcher)
    {
        $defaultNamespace = $dispatcher->getDefaultNamespace();
        $namespace = $dispatcher->getNamespaceName();

        if (strpos($namespace, $defaultNamespace) === 0) {
            $namespace = substr($namespace, strlen($defaultNamespace));
        }

        $parts   = array_filter(explode('\\', $namespace));
        $parts[] = $dispatcher->getControllerName();
        $parts[] = $dispatcher->getActionName();

        $this->view->pick(implode(DIRECTORY_SEPARATOR, $parts));
    }
}
