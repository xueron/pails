<?php
/**
 * AbstractService.php
 *
 */


namespace Pails\Services;


use Phalcon\Di\Injectable;
use Phalcon\DiInterface;

abstract class AbstractService extends Injectable implements ServiceInterface
{
    /**
     * AbstractServiceProvider constructor.
     *
     * @param DiInterface $di The Dependency Injector.
     */
    public function __construct(DiInterface $di)
    {
        $this->setDI($di);
    }
}
