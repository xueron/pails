<?php
/**
 * ProviderInterface.php
 *
 */
namespace Pails\Providers;

use Phalcon\Di\InjectionAwareInterface;

interface ServiceProviderInterface extends InjectionAwareInterface
{
    /**
     * Register application service.
     *
     * @return mixed
     */
    public function register();

    /**
     * Gets the Service name.
     *
     * @return string
     */
    public function getName();
}
