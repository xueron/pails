<?php
namespace Pails\Plugins;

use Pails\Injectable;
use Phalcon\Events\EventInterface;
use Phalcon\Mvc\DispatcherInterface;

/**
 * Class CustomRender
 * @package Pails\Plugins
 */
class CustomRender extends Injectable
{
    /**
     * @param EventInterface $event
     * @param DispatcherInterface $dispatcher
     */
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
