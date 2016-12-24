<?php
/**
 * Controller.php
 *
 */

namespace Pails\Mvc;
use Pails\Plugins\Fractal;
use Pails\Plugins\Inflector;
use Phalcon\Logger\AdapterInterface;

/**
 * Class Controller
 * @package Pails\Mvc
 * @property \Pails\Plugins\ApiResponse $apiResponse
 * @property \Phalcon\Security\Random $random
 * @property Fractal $fractal
 * @property Inflector $inflector
 * @property AdapterInterface $logger
 */
abstract class Controller extends \Phalcon\Mvc\Controller
{

}
