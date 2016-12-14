<?php
/**
 * RandomServiceProvider.php
 *
 */


namespace Pails\Providers;

use Phalcon\Security\Random;

class RandomServiceProvider extends AbstractServiceProvider
{
    protected $serviceName = 'random';

    function register()
    {
        $this->getDI()->setShared(
            $this->serviceName,
            Random::class
        );
    }
}
