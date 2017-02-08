<?php
/**
 * InflectorServiceProvider.php
 *
 */
namespace Pails\Providers;

use Pails\Pluralizer;

class InflectorServiceProvider extends AbstractServiceProvider
{
    protected $serviceName = 'inflector';

    public function register()
    {
        $this->getDI()->setShared(
            $this->serviceName,
            Pluralizer::class
        );
    }
}
