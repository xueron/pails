<?php
/**
 * RollbackCommand.php
 *
 */


namespace Pails\Console\Commands\Db;


use Phinx\Console\Command\Rollback;

class RollbackCommand extends Rollback
{
    protected function configure()
    {
        parent::configure();

        $this->setName("db:rollback");
    }
}
