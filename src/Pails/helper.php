<?php
use Phalcon\Di;
use Phalcon\Text;

if (!function_exists('app')) {
    /**
     * 返回一个应用容器
     *
     * @param null $name
     * @param array $parameters
     * @return mixed|\Phalcon\DI\FactoryDefault|Di\FactoryDefault\Cli
     */
    function app($name = null, $parameters = [])
    {
        // 利用 \Phalcon\Di 自带的 static 容器
        if (!$app = Di::getDefault()) {
            throw new \RuntimeException("Application not initialized");
        }

        if (!is_null($name)) {
            return $app->get($name, $parameters);
        }

        return $app;
    }
}

if (!function_exists('env')) {
    /**
     * Gets the value of an environment variable. Supports boolean, empty and null.
     *
     * @param  string $key
     * @param  mixed $default
     * @return mixed
     */
    function env($key, $default = null)
    {
        $value = getenv($key);

        if ($value === false) {
            return value($default);
        }

        switch (strtolower($value)) {
            case 'true':
            case '(true)':
                return true;

            case 'false':
            case '(false)':
                return false;

            case 'empty':
            case '(empty)':
                return '';

            case 'null':
            case '(null)':
                return;
        }

        if (Text::startsWith($value, '"') && Text::endsWith($value, '"')) {
            return substr($value, 1, -1);
        }

        return $value;
    }
}

if (!function_exists('value')) {
    /**
     * Return the default value of the given value.
     *
     * @param  mixed $value
     * @return mixed
     */
    function value($value)
    {
        return $value instanceof Closure ? $value() : $value;
    }
}

if (!function_exists('with')) {
    /**
     * Return the given object. Useful for chaining.
     *
     * @param  mixed $object
     * @return mixed
     */
    function with($object)
    {
        return $object;
    }
}

if (!function_exists('is_json')) {
    /**
     * 判断一个字符串是否是合法的json字符串
     * @param $string
     * @return bool
     */
    function is_json($string)
    {
        if (is_string($string)) {
            @json_decode($string);
            return (json_last_error() === JSON_ERROR_NONE);
        }
        return false;
    }
}
