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
    public function __call($method, $args)
    {
        $message = array_shift($args);
        if (is_resource($message)) {
            $message = sprintf("resource-type=%s", get_resource_type($message));
        } elseif (is_object($message)) {
            $message = var_export($message, 1);
        } else {
            if (!is_scalar($message)) {
                $message = serialize($message);
            }
        }

        $file = '';
        $line = '';
        $class = static::class;
        $function = '';
        if (defined('APP_DEBUG') && APP_DEBUG) {
            $trace = debug_backtrace();
            $_first = array_shift($trace);
            $_second = array_shift($trace);
            if ($_first) {
                $file = $_first['file'];
                $line = $_first['line'];
            }
            if ($_second) {
                $class = $_second['class'];
                $function = $_second['function'];
            }
            $message = sprintf("[%s::%s] [file=%s line=%s] %s", $class, $function, $file, $line, $message);
        } else {
            $message = sprintf("[%s] %s", $class, $message);
        }
        array_unshift($args, $message);

        try {
            return call_user_func_array([$this->logger, $method], $args);
        } catch (\Exception $e) {
            error_log($message);
        }
    }
}
