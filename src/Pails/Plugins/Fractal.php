<?php
/**
 * Fractal.php
 *
 * a wrap for League\Fractal, to make data with transformers
 *
 */
namespace Pails\Plugins;

use League\Fractal\Manager;
use League\Fractal\Pagination\Cursor;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use Phalcon\Mvc\User\Plugin;

class Fractal extends Plugin
{
    /**
     * @return Manager
     */
    public function getManager()
    {
        return $this->getDI()->getShared(Manager::class);
    }

    public function item($data, $transformer, $resourceKey = null, $meta = [])
    {
        if (!is_object($transformer) && !is_callable($transformer)) {
            $transformer = $this->getDI()->getShared($transformer);
        }
        $resource = new Item($data, $transformer, $resourceKey);

        foreach ($meta as $metaKey => $metaValue) {
            $resource->setMetaValue($metaKey, $metaValue);
        }

        $rootScope = $this->getManager()->createData($resource);

        return $rootScope->toArray();
    }

    public function collection($data, $transformer, $resourceKey = null, Cursor $cursor = null, $meta = [])
    {
        if (!is_object($transformer) && !is_callable($transformer)) {
            $transformer = $this->getDI()->getShared($transformer);
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
