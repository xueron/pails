<?php
/**
 * SeedCreateCommand.php
 *
 */


namespace Pails\Console\Commands\Db;


use Phinx\Console\Command\SeedCreate;

class SeedCreateCommand extends SeedCreate
{
    protected function configure()
    {
        parent::configure();

        $this->setName("seed:create");
        $this->setDescription("创建一个数据库seeder");

    }
}
