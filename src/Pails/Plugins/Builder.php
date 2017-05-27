<?php
/**
 * Builder.php
 *
 */
namespace Pails\Plugins;

/**
 * Class Builder
 *
 * @package Pails\Plugins
 */
class Builder extends \Phalcon\Mvc\Model\Query\Builder
{
    /**
     * @param int $page
     * @param int $limit
     *
     * @return mixed
     */
    public function getPage($page = 1, $limit = 10)
    {
        $options = [
            'builder' => $this,
            'limit'   => $limit,
            'page'    => $page,
        ];

        return $this->getDI()->get(Paginator::class, [$options]);
    }

    /**
     * @param       $functionName
     * @param       $alias
     * @param array $options
     *
     * @return mixed
     */
    protected function _groupResult($functionName, $alias, $options = [])
    {
        $builder = clone $this;

        if ($options['column']) {
            $groupColumn = $options['column'];
        } else {
            $groupColumn = '*';
        }

        if ($distinct = $options['distinct']) {
            $columns = $functionName . '(DISTINCT ' . $groupColumn . ') AS ' . $alias;
        } else {
            if ($groups = $builder->getGroupBy()) {
                if (!is_array($groups)) {
                    $groups = [ $groups ];
                }
                $columns = implode(', ', $groups) . ', ' . $functionName . '(' . $groupColumn . ') AS ' . $alias;
            } else {
                $columns = $functionName . '(' . $groupColumn . ') AS ' . $alias;
            }
        }
        $builder->columns($columns);
        $builder->orderBy(null);

        $query = $builder->getQuery();
        $resultset = $query->execute();

        if ($builder->getGroupBy()) {
            return $resultset;
        }

        $firstRow = $resultset->getFirst();
        return $firstRow->{$alias};
    }

    /**
     * @param      $column
     * @param bool $distinct
     *
     * @return mixed
     */
    public function count($column, $distinct = false)
    {
        return $this->_groupResult('COUNT', 'rowcount', ['column' => $column, 'distinct' => $distinct]);
    }

    /**
     * @param $column
     *
     * @return mixed
     */
    public function sum($column)
    {
        return $this->_groupResult('SUM', 'sumatory', ['column' => $column]);
    }

    /**
     * @param $column
     *
     * @return mixed
     */
    public function avg($column)
    {
        return $this->_groupResult('AVG', 'average', ['column' => $column]);
    }

    /**
     * @param $column
     *
     * @return mixed
     */
    public function min($column)
    {
        return $this->_groupResult('MIN', 'minimum', ['column' => $column]);

    }

    /**
     * @param $column
     *
     * @return mixed
     */
    public function max($column)
    {
        return $this->_groupResult('MAX', 'maximum', ['column' => $column]);

    }

    /**
     * @param $column
     *
     * @return mixed
     */
    public function calc($column)
    {
        $builder = clone $this;
        $builder->columns($column);

        $query = $builder->getQuery();
        $resultset = $query->execute();
        return $resultset->getFirst();
    }
}
