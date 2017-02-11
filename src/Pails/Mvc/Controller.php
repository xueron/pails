<?php
/**
 * Controller.php
 *
 */
namespace Pails\Mvc;

/**
 * Class Controller
 * @package Pails\Mvc
 * @property \Pails\Plugins\Config $config
 * @property \Pails\Pluralizer $inflector
 * @property \Pails\Plugins\ApiResponse $apiResponse
 * @property \Pails\Plugins\Fractal $fractal
 * @property \Phalcon\Security\Random $random
 * @property \Phalcon\Logger\AdapterInterface $logger
 * @property \Phalcon\Logger\AdapterInterface $errorLogger
 * @property \Phalcon\Cache\BackendInterface $cache
 */
abstract class Controller extends \Phalcon\Mvc\Controller
{

}
