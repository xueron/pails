<?php
/**
 * Paginator.php
 *
 */
namespace Pails\Plugins;

use Phalcon\Paginator\Adapter\QueryBuilder;

class Paginator extends QueryBuilder
{
    protected $_data = null;

    protected $_meta = null;

    protected $_paginator = null;

    /**
     * @return null|\stdClass
     */
    public function getPaginate()
    {
        if ($this->_paginator == null) {
            $this->_paginator = parent::getPaginate();
        }
        return $this->_paginator;
    }

    /**
     * @return null
     */
    public function getData()
    {
        if ($this->_paginator == null) {
            $this->_paginator = $this->getPaginate();
        }
        if ($this->_data == null) {
            $this->_data = $this->_paginator->items;
        }
        return $this->_data;
    }

    /**
     * @return array|null
     */
    public function getMeta()
    {
        if ($this->_paginator == null) {
            $this->_paginator = $this->getPaginate();
        }
        if ($this->_meta == null) {
            $this->_meta = [
                'first' => $this->_paginator->first,
                'before' => $this->_paginator->before,
                'current' => $this->_paginator->current,
                'last' => $this->_paginator->last,
                'next' => $this->_paginator->next,
                'total_pages' => $this->_paginator->total_pages,
                'total_items' => $this->_paginator->total_items,
                'limit' => $this->_paginator->limit
            ];
        }
        return $this->_meta;
    }
}
