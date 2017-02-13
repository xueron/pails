<?php
namespace Pails\Console\Commands\Db;


use Phinx\Console\Command\Create;

class CreateCommand extends Create
{
    protected function configure()
    {
        parent::configure();

        $this->setName('db:create');

        $this->setDescription("新建一个数据库迁移");
    }
}
