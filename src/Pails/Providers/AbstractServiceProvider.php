<?php
/**
 * AbstructProvider.php
 *
 */
namespace Pails\Providers;

use Pails\Injectable;
use Phalcon\Di\InjectionAwareInterface;

/**
 * Class AbstractServiceProvider
 * @package Pails\Providers
 */
abstract class AbstractServiceProvider extends Injectable  implements ServiceProviderInterface, InjectionAwareInterface
{
    /**
     * @return mixed
     */
    abstract function register();
}
