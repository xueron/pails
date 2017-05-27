<?php
/**
 * Model.php
 */

namespace Pails\Mvc;

use Phalcon\Di;
use Phalcon\Mvc\Model as PhalconModel;
use Phalcon\Mvc\Model\Behavior\Timestampable;

PhalconModel::setup([
    'exceptionOnFailedSave' => true, // 启用异常
    'ignoreUnknownColumns'  => true,  // 忽略不存在的字段
]);

abstract class Model extends PhalconModel
{
    public function initialize()
    {
        // 自动更新时间戳字段
        $this->addBehavior(new Timestampable([
                'beforeValidationOnCreate' => [
                    'field'  => ['created_at', 'updated_at'],
                    'format' => 'Y-m-d H:i:s',
                ],
                'beforeValidationOnUpdate' => [
                    'field'  => 'updated_at',
                    'format' => 'Y-m-d H:i:s',
                ],
            ])
        );
    }

    /**
     * 表名前缀、是否自动复数化变得可配
     *
     * @return string
     */
    public function getSource()
    {
        $source = parent::getSource();

        if (true === $this->getDI()->get('config')->get('db.use_plural')) {
            $source = str_plural($source);
        }

        if ($prefix = $this->getDI()->get('config')->get('db.prefix')) {
            return $prefix . $source;
        }

        return $source;
    }

    /**
     * @param array $data
     *
     * @return static
     */
    public static function make($data = [])
    {
        // filter empty values
        $data = array_filter($data);

        // remove auto data;
        unset($data['id']);
        unset($data['created_at']);
        unset($data['updated_at']);

        //
        $model = new static($data);

        return $model;
    }

    /**
     * 跟find()类似, 包含总数、页数、上一页、下一页等信息
     *
     * @param mixed $parameters
     * @param int   $limit
     * @param int   $page
     *
     * @return \Pails\Plugins\Paginator
     */
    public static function list($parameters = null, $page = 1, $limit = 20)
    {
        // static function.
        $di = Di::getDefault();
        $manager = $di->getShared('modelsManager');

        //
        if ($parameters instanceof PhalconModel\Query\BuilderInterface) {
            $builder = $parameters;
        } else {
            if (!is_array($parameters)) {
                $params[] = $parameters;
            } else {
                $params = $parameters;
            }
            //
            $builder = $manager->createBuilder($params);
            $builder->from(get_called_class());

            if (isset($params['limit'])) {
                $limit = $params['limit'];
            }
        }

        $options = [
            'builder' => $builder,
            'limit'   => $limit,
            'page'    => $page,
        ];

        return $di->get('Pails\\Plugins\\Paginator', [$options]);
    }
}
