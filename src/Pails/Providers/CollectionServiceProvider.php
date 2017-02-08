<?php
/**
 * CollectionServiceProvider.php
 *
 */
namespace Pails\Providers;

use Pails\Collection;

class CollectionServiceProvider extends AbstractServiceProvider
{
    protected $serviceName = 'collection';

    public function register()
    {
        $this->getDI()->setShared(
            $this->serviceName,
            Collection::class
        );
    }
}
