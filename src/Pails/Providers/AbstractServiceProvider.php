<?php
/**
 * AbstructProvider.php
 *
 */
namespace Pails\Providers;

use Pails\ContainerInterface;
use Pails\Injectable;
use Phalcon\Di\InjectionAwareInterface;
use Phalcon\DiInterface;

/**
 * Class AbstractServiceProvider
 * @package Pails\Providers
 */
abstract class AbstractServiceProvider extends Injectable  implements ServiceProviderInterface, InjectionAwareInterface
{
    /**
     * AbstractServiceProvider constructor.
     *
     * @param ContainerInterface|DiInterface $di
     */
    public function __construct(ContainerInterface $di)
    {
        $this->setDI($di);
    }

    /**
     * @return mixed
     */
    abstract function register();
}
