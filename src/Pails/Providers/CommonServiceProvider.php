<?php
namespace Pails\Providers;

use GuzzleHttp\Client;
use League\Flysystem\Filesystem;
use Pails\Exception\Handler;
use Pails\Plugins\ApiResponse;
use Pails\Plugins\Config;
use Pails\Plugins\Fractal;
use Pails\Pluralizer;

class CommonServiceProvider extends AbstractServiceProvider implements ServiceProviderInterface
{
    function register()
    {
        $di = $this->getDI();

        // apiResponse
        $di->setShared(
            'apiResponse',
            ApiResponse::class
        );

        // config
        $di->setShared(
            'config',
            Config::class
        );

        // fractal
        $di->setShared(
            'fractal',
            Fractal::class
        );

        // inflector
        $di->setShared(
            'inflector',
            Pluralizer::class
        );

        // httpClient
        $di->setShared(
            'httpClient',
            Client::class
        );

        // exceptionHandler
        $di->setShared(
            'exceptionHandler',
            Handler::class
        );

        // filesystem
        $di->setShared(
            'filesystem',
            function () {
                if ($this->di->has('localFile')) {
                    $local = new Filesystem($this->di->get("localFile"));
                }


            }
        );
    }
}
