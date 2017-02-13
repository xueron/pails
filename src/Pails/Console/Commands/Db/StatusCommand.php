<?php
/**
 * StatusCommand.php
 *
 */


namespace Pails\Console\Commands\Db;


use Phinx\Console\Command\Status;

class StatusCommand extends Status
{
    protected function configure()
    {
        parent::configure();

        $this->setName("db:status");
        $this->setDescription("显示数据库迁移状态");

    }
}
