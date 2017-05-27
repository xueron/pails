<?php
/**
 * Builder.php
 *
 */
namespace Pails\Plugins;

class Builder extends \Phalcon\Mvc\Model\Query\Builder
{
    public function getPaginator($page = 1, $limit = 10)
    {
        $options = [
            'builder' => $this,
            'limit'   => $limit,
            'page'    => $page,
        ];

        return $this->getDI()->get(Paginator::class, [$options]);
    }
}
