<?php
/**
 * ApiResponse.php
 *
 */
namespace Pails\Providers;

use Pails\Plugins\ApiResponse;

class ApiResponseServiceProvider extends AbstractServiceProvider
{
    protected $serviceName = 'apiResponse';

    public function register()
    {
        $this->getDI()->setShared(
            $this->serviceName,
            ApiResponse::class
        );
    }
}
