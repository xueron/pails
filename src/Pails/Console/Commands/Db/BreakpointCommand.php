<?php
/**
 * BreakpointCommand.php
 *
 */


namespace Pails\Console\Commands\Db;


use Phinx\Console\Command\Breakpoint;

class BreakpointCommand extends Breakpoint
{
    public function configure()
    {
        parent::configure();

        $this->setName("db:breakpoint");
    }
}
