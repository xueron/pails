<?php
/**
 * Fractal.php
 *
 * a wrap for League\Fractal, to make data with transformers
 */
namespace Pails\Plugins;

use League\Fractal\Manager;
use League\Fractal\Pagination\Cursor;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use Pails\Injectable;

/**
 * Class Fractal
 *
 * @package Pails\Plugins
 */
class Fractal extends Injectable
{
    /**
     * @return Manager
     */
    public function getManager()
    {
        return $this->di->getShared(Manager::class);
    }

    /**
     * @param $data
     * @param $transformer
     * @param null  $resourceKey
     * @param array $meta
     *
     * @return array
     */
    public function item($data, $transformer, $resourceKey = null, $meta = [])
    {
        if (!is_object($transformer) && !is_callable($transformer)) {
            $transformer = $this->di->getShared($transformer);
        }
        $resource = new Item($data, $transformer, $resourceKey);

        foreach ($meta as $metaKey => $metaValue) {
            $resource->setMetaValue($metaKey, $metaValue);
        }

        $rootScope = $this->getManager()->createData($resource);

        return $rootScope->toArray();
    }

    /**
     * @param $data
     * @param $transformer
     * @param null        $resourceKey
     * @param Cursor|null $cursor
     * @param array       $meta
     *
     * @return array
     */
    public function collection($data, $transformer, $resourceKey = null, Cursor $cursor = null, $meta = [])
    {
        if (!is_object($transformer) && !is_callable($transformer)) {
            $transformer = $this->di->getShared($transformer);
        }

        $resource = new Collection($data, $transformer, $resourceKey);

        foreach ($meta as $metaKey => $metaValue) {
            $resource->setMetaValue($metaKey, $metaValue);
        }

        if (!is_null($cursor)) {
            $resource->setCursor($cursor);
        }

        $rootScope = $this->getManager()->createData($resource);

        return $rootScope->toArray();
    }
}
