<?php
/**
 * ModelManager.php
 *
 */


namespace Pails\Mvc;


use Phalcon\Mvc\Model\Manager;

class ModelManager extends Manager
{
    /**
     * 处理读写分离
     *
     * @param \Phalcon\Mvc\ModelInterface $model
     *
     * @return mixed|\Phalcon\Db\AdapterInterface
     */
    public function getReadConnection(\Phalcon\Mvc\ModelInterface $model)
    {
        $di = $this->getDI();
        if ((true === $di->get('config')->get('db.use_slave')) && $di->has('dbRead')) {
            return $di->getShared('dbRead');
        }

        return parent::getReadConnection($model);
    }
}
