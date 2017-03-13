<?php
/**
 * RollbackCommand.php
 */
namespace Pails\Console\Commands\Db;

use Phinx\Console\Command\Rollback;

class RollbackCommand extends Rollback
{
    use ExecuteTrait;

    protected function configure()
    {
        parent::configure();

        $this->setName('db:rollback');
        $this->setDescription('回滚最后一个迁移，或者回滚到指定的迁移记录');
    }
}
