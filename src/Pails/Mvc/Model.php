<?php
namespace Pails\Mvc;

abstract class Model extends \Phalcon\Mvc\Model
{
    public function getSource()
    {
        $source = parent::getSource();
        if ($this->di->get('inflector')) {
            return $this->di->get('inflector')->pluralize($source);
        }
        return $source;
    }
}
