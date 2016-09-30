<?php
/**
 * ApiResponse.php
 *
 */


namespace Pails\Bootstraps;


use League\Fractal\Manager;
use Pails\Container;

class ApiResponse
{
    public function boot(Container $container)
    {
        $container->setShared('apiResponse', function () {
            $manager = $this->getShared(Manager::class);
            $apiResponse = new \Pails\Plugins\ApiResponse($manager);
            $apiResponse->setDI($this);
            return $apiResponse;
        });
    }
}
