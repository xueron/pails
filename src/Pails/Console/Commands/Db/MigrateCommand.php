<?php
/**
 * MigrateCommand.php
 *
 */


namespace Pails\Console\Commands\Db;


use Phinx\Console\Command\Migrate;

class MigrateCommand extends Migrate
{
    protected function configure()
    {
        parent::configure();

        $this->setName("db:migrate");
    }
}
