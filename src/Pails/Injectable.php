<?php
/**
 * Injectable.php
 *
 * A wrap for phalcon's injectable abstract. Add some properties.
 */


namespace Pails;

use Phalcon\Logger\Adapter;

/**
 * Class Injectable
 * @package Pails
 *
 * @property \Pails\Plugins\ApiResponse $apiResponse
 * @property \Pails\Collection\Collection collection
 * @property \Pails\Plugins\Fractal $fractal
 * @property \Pails\Plugins\Inflector $inflector
 * @property \Phalcon\Logger\Adapter\File $logger
 * @method \Phalcon\Logger\AdapterInterface error($message, array $context = [])
 * @method \Phalcon\Logger\AdapterInterface info($message, array $context = [])
 * @method \Phalcon\Logger\AdapterInterface debug($message, array $context = [])
 * @method \Phalcon\Logger\AdapterInterface notice($message, array $context = [])
 * @method \Phalcon\Logger\AdapterInterface critical($message, array $context = [])
 * @method \Phalcon\Logger\AdapterInterface emergency($message, array $context = [])
 * @method \Phalcon\Logger\AdapterInterface warning($message, array $context = [])
 * @method \Phalcon\Logger\AdapterInterface alert($message, array $context = [])
 */
abstract class Injectable extends \Phalcon\Di\Injectable
{
    public function __call($method, $message)
    {
        $class = static::class;
        if (is_resource($message)) {
            $message = sprintf("resource-type=%s", get_resource_type($message));
        } else {
            if (!is_scalar($message)) {
                $message = serialize($message);
            }
        }
        $message = sprintf("[%s] %s", $class, $message);

        if (isset($this->logger) && ($this->logger instanceof Adapter)) {
            if (method_exists($this->logger, $method)) {
                return $this->logger->$method($message);
            } else {
                error_log("method $method not supported");
                error_log($message);
            }
        } else {
            error_log($message);
        }
    }
}
