<?php
/**
 * Model.php
 *
 */
namespace Pails\Mvc;

use Phalcon\Di;
use Phalcon\Mvc\Model as PhalconModel;
use Phalcon\Mvc\Model\Behavior\Timestampable;

PhalconModel::setup([
    'exceptionOnFailedSave' => true, // 启用异常
]);

abstract class Model extends PhalconModel
{

    public function initialize()
    {
        // 自动更新时间戳字段
        $this->addBehavior(new Timestampable([
                'beforeValidationOnCreate' => [
                    'field' => ['created_at', 'updated_at'],
                    'format' => 'Y-m-d H:i:s'
                ],
                'beforeValidationOnUpdate' => [
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
        $model->save($data);
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

        return $di->get("Pails\\Plugins\\Paginator", [$params]);

        // remove
        $paginator = $di->get("Pails\\Plugins\\Paginator", [$params]);
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
    public static function show($id, $transformer = null)
    {
        $item = static::findFirst($id);
        if ($item) {
            return $item->toArray();
        }
        return false;
    }
}
