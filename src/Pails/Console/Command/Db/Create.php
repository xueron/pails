<?php
namespace Pails\Console\Command\Db;


class Create extends \Phinx\Console\Command\Create
{
    protected function configure()
    {
        parent::configure();
        $this->setName('db:create');
    }

}
