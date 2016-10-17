<?php
/**
 * FractalServiceProvider.php
 *
 */


namespace Pails\Providers;


use Pails\Plugins\Fractal;

class FractalServiceProvider extends AbstractServiceProvider
{
    protected $serviceName = 'fractal';

    public function register()
    {
        $this->getDI()->setShared(
            $this->serviceName,
            Fractal::class
        );
    }
}
