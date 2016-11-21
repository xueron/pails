<?php
/**
 * AbstructProvider.php
 *
 */
namespace Pails\Providers;

use Phalcon\Di\Injectable;
use Phalcon\DiInterface;

abstract class AbstractServiceProvider extends Injectable  implements ServiceProviderInterface
{
    /**
     * The Service name.
     * @var string
     */
    protected $serviceName;

    /**
     * AbstractServiceProvider constructor.
     *
     * @param DiInterface $di The Dependency Injector.
     */
    public function __construct(DiInterface $di)
    {
        $this->setDI($di);
    }

    /**
     * Gets the Service name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->serviceName;
    }

    abstract function register();
}
