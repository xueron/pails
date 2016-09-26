<?php
/**
 * Model.php
 *
 */
namespace Pails\Mvc;

use Phalcon\Di;
use Phalcon\Mvc\Model\Behavior\Timestampable;

abstract class Model extends \Phalcon\Mvc\Model
{

    public function initialize()
    {
        // 自动更新时间戳字段
        $this->addBehavior(new Timestampable([
                'beforeCreate' => [
                    'field' => ['updated_at'],
                    'format' => 'Y-m-d H:i:s'
                ],
                'beforeUpdate' => [
                    'field' => 'updated_at',
                    'format' => 'Y-m-d H:i:s'
                ]
            ])
        );
    }

    public static function make($data = [])
    {
        // filter empty values
        $data = array_filter($data);

        // remove auto data;
        unset($data['id']);
        unset($data['created_at']);
        unset($data['updated_at']);

        //
        $model = new static();
        if ($model->save($data)) {
            return $model;
        }
        return false;
    }

    /**
     * 跟find()类似,包含总数、页数、上一页、下一页等信息
     *
     * @param $parameters
     * @param int $limit
     * @param int $page
     * @return mixed
     */
    public static function list($parameters = null, $page = 1, $limit = 20)
    {
        // static function.
        $di = Di::getDefault();

        //
        if (!is_array($parameters)) {
            $params[] = $parameters;
        } else {
            $params = $parameters;
        }

        $manager = $di->getShared('modelsManager');
        $builder = $manager->createBuilder($params);
        $builder->from(get_called_class());

        if (isset($params['limit'])) {
            $limit = $params['limit'];
        }

        $params = [
            "builder" => $builder,
            "limit" => $limit,
            "page" => $page
        ];

        $paginator = $di->get("Phalcon\\Paginator\\Adapter\\QueryBuilder", [$params]);
        $data = $paginator->getPaginate();

        $items = [];
        foreach ($data->items as $item) {
            $items[] = $item->toArray();
        }

        $data = (array)$data;
        $data['items'] = $items;

        return $data;
    }

    /**
     * @param $id
     * @return array|bool
     */
    public static function show($id)
    {
        $item = static::findFirst($id);
        if ($item) {
            return $item->toArray();
        }
        return false;
    }
}
