<?php
/**
 * AbstractService.php
 *
 */


namespace Pails\Services;


use Pails\ContainerInterface;
use Phalcon\Di\Injectable;
use Phalcon\DiInterface;

abstract class AbstractService extends Injectable implements ServiceInterface
{
    /**
     * AbstractServiceProvider constructor.
     *
     * @param ContainerInterface|DiInterface $di The Dependency Injector.
     */
    public function __construct(ContainerInterface $di)
    {
        $this->setDI($di);
    }
}
