<?php
namespace Pails\Extension;
/**
 * Created by PhpStorm.
 * User: nishurong
 * Date: 16/4/15
 * Time: 下午4:26
 */
class Volt
{
    /**
     * This method is called on any attempt to compile a function call
     */
    public function compileFunction()
    {
        $params = func_get_args();
        $name = array_shift($params);
        array_pop($params);
        if (function_exists($name)) {
            return $name . '('. join(", ", $params) . ')';
        }
    }
} // End Volt
