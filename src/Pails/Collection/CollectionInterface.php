<?php
namespace Pails\Collection;

interface CollectionInterface extends \IteratorAggregate, \ArrayAccess
{
    /**
     * @param string $name
     * @return mixed
     */
    public function get($name);

    /**
     * @param string $name
     * @param mixed $object
     */
    public function set($name, $object);

    /**
     * @param string $name
     * @return mixed
     */
    public function has($name);
}
