<?php
/**
 * InflectorServiceProvider.php
 *
 */
namespace Pails\Providers;

use Pails\Plugins\Inflector;

class InflectorServiceProvider extends AbstractServiceProvider
{
    protected $serviceName = 'inflector';

    public function register()
    {
        $this->getDI()->setShared(
            $this->serviceName,
            Inflector::class
        );
    }
}
