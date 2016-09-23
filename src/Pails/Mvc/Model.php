<?php
/**
 * Model.php
 *
 */
namespace Pails\Mvc;

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
}
