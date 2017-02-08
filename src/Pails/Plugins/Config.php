<?php
/**
 * Config.php
 *
 */
namespace Pails\Plugins;

use Pails\Arr;
use Pails\Injectable;

class Config extends Injectable implements \ArrayAccess
{
    protected $_env = 'development';

    protected $_sections = [];

    public function __construct()
    {
        // INIT env
        if ($env = env("APP_ENV", "development")) {
            $this->_env = $env;
        }
    }

    public function getConfig($section)
    {
        if (!isset($this->_sections[$section])) {
            $this->_sections[$section] = $this->getDI()->getConfig($section, $this->_env, []);
        }
        return $this->_sections[$section];
    }

    public function has($key)
    {
        list($section, $name) = explode('.', $key, 2);
        return Arr::has($this->getConfig($section), $name);
    }

    public function get($key, $default = null)
    {
        list($section, $name) = explode('.', $key, 2);
        return Arr::get($this->getConfig($section), $name, $default);
    }

    public function set($key, $value = null)
    {
        $keys = is_array($key) ? $key : [$key => $value];

        foreach ($keys as $key => $value) {
            list($section, $name) = explode('.', $key, 2);
            $this->getConfig($section);

            Arr::set($this->_sections[$section], $name, $value);
        }
    }

    public function prepend($key, $value)
    {
        $array = $this->get($key);

        array_unshift($array, $value);

        $this->set($key, $array);
    }

    public function push($key, $value)
    {
        $array = $this->get($key);

        $array[] = $value;

        $this->set($key, $array);
    }

    public function offsetExists($offset)
    {
        return $this->has($offset);
    }

    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    public function offsetSet($offset, $value)
    {
        $this->set($offset, $value);
    }

    public function offsetUnset($offset)
    {
        $this->set($offset, null);
    }
}
