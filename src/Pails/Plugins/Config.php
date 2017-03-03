<?php
/**
 * Config.php
 *
 */
namespace Pails\Plugins;

use Pails\Arr;
use Pails\Injectable;

/**
 * Class Config
 * @package Pails\Plugins
 */
class Config extends Injectable implements \ArrayAccess
{
    /**
     * @var array
     */
    protected $_sections = [];

    /**
     * @param $section
     * @return mixed
     */
    public function getConfig($section)
    {
        if (!isset($this->_sections[$section])) {
            $this->_sections[$section] = $this->di->getConfig($section, $this->di->environment(), []);
        }
        return $this->_sections[$section];
    }

    /**
     * @param $key
     * @return bool
     */
    public function has($key)
    {
        list($section, $name) = explode('.', $key, 2);
        return Arr::has($this->getConfig($section), $name);
    }

    /**
     * @param $key
     * @param null $default
     * @return mixed
     */
    public function get($key, $default = null)
    {
        list($section, $name) = explode('.', $key, 2);
        return Arr::get($this->getConfig($section), $name, $default);
    }

    /**
     * @param $key
     * @param null $value
     */
    public function set($key, $value = null)
    {
        $keys = is_array($key) ? $key : [$key => $value];

        foreach ($keys as $key => $value) {
            list($section, $name) = explode('.', $key, 2);
            $this->getConfig($section);

            Arr::set($this->_sections[$section], $name, $value);
        }
    }

    /**
     * @param $key
     * @param $value
     */
    public function prepend($key, $value)
    {
        $array = $this->get($key);

        array_unshift($array, $value);

        $this->set($key, $array);
    }

    /**
     * @param $key
     * @param $value
     */
    public function push($key, $value)
    {
        $array = $this->get($key);

        $array[] = $value;

        $this->set($key, $array);
    }

    /**
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return $this->has($offset);
    }

    /**
     * @param mixed $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value)
    {
        $this->set($offset, $value);
    }

    /**
     * @param mixed $offset
     */
    public function offsetUnset($offset)
    {
        $this->set($offset, null);
    }
}
