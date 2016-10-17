<?php
namespace Pails\Plugins;
class VoltExtension
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
}
