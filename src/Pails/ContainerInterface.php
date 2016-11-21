<?php
/**
 * ContainerInterface.php
 *
 */


namespace Pails;


use Pails\Providers\ServiceProviderInterface;
use Phalcon\DiInterface;

interface ContainerInterface extends DiInterface
{
    public function registerServices($serviceProviders = []);
    public function registerService(ServiceProviderInterface $serviceProvider);
    public function version();
    public function setBasePath($basePath);
    public function path($dir);
    public function basePath($dir);
    public function appPath();
    public function configPath();
    public function databasePath();
    public function libPath();
    public function logPath();
    public function tmpPath();
    public function storagePath();
    public function resourcesPath();
    public function viewsPath();
    public function getConfig($section, $key = null, $defaultValue = null);
    public function run($appClass);
    public function renderException(\Exception $e);
    public function reportException(\Exception $e);
}
