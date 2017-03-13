<?php
/**
 * ProviderInterface.php
 */
namespace Pails\Providers;

interface ServiceProviderInterface
{
    /**
     * Register application service.
     *
     * @return mixed
     */
    public function register();
}
