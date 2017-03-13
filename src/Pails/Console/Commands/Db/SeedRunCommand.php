<?php
/**
 * SeedRunCommand.php
 */
namespace Pails\Console\Commands\Db;

use Phinx\Console\Command\SeedRun;

class SeedRunCommand extends SeedRun
{
    use ExecuteTrait;

    protected function configure()
    {
        parent::configure();

        $this->setName('seed:run');
        $this->setDescription('执行数据库seed');
    }
}
