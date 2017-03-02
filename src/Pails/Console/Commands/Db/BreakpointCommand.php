<?php
/**
 * BreakpointCommand.php
 *
 */
namespace Pails\Console\Commands\Db;

use Phinx\Console\Command\Breakpoint;

class BreakpointCommand extends Breakpoint
{
    use ExecuteTrait;

    public function configure()
    {
        parent::configure();

        $this->setName("db:breakpoint");
        $this->setDescription("管理数据库迁移的断点");
    }
}
