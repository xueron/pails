<?php
/**
 * AbstructProvider.php
 *
 */
namespace Pails\Providers;

use Pails\ContainerInterface;
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
     * @param ContainerInterface|DiInterface $di
     */
    public function __construct(ContainerInterface $di)
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
