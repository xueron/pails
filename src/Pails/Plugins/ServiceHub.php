<?php
/**
 * ServiceHub.php
 */

namespace Pails\Plugins;

use Pails\Container;
use Pails\Injectable;
use Phalcon\Mvc\Model\Query\BuilderInterface;

/**
 * Class ServiceHub
 *
 * @package Pails\Plugins
 */
class ServiceHub extends Injectable
{
    /**
     * @return \Phalcon\DiInterface
     */
    public static function di()
    {
        return Container::getDefault();
    }

    /**
     * @param mixed $params
     *
     * @return \Phalcon\Mvc\Model\Query\BuilderInterface
     */
    public static function builder($params = null)
    {
        return static::di()->get(Builder::class, [$params, static::di()]);
    }

    /**
     * @param \Phalcon\Mvc\Model\Query\BuilderInterface $builder
     * @param int                                       $page
     * @param int                                       $limit
     *
     * @return \Pails\Plugins\Paginator
     */
    public static function list(BuilderInterface $builder, $page = 1, $limit = 10)
    {
        $options = [
            'builder' => $builder,
            'limit'   => $limit,
            'page'    => $page,
        ];

        return static::di()->get(Paginator::class, [$options]);
    }
}
