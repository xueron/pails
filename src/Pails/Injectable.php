<?php
/**
 * Injectable.php
 *
 * A wrap for phalcon's injectable abstract. Add some properties.
 */


namespace Pails;

/**
 * Class Injectable
 * @package Pails
 *
 * @property \Pails\Plugins\ApiResponse $apiResponse
 * @property \Pails\Collection\Collection collection
 * @property \Pails\Plugins\Fractal $fractal
 * @property \Pails\Plugins\Inflector $inflector
 * @property \Phalcon\Logger\Adapter\File $logger
 */
abstract class Injectable extends \Phalcon\Di\Injectable
{

}
