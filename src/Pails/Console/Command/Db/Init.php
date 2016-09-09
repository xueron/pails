<?php
namespace Pails\Console\Command\Db;


class Init extends \Phinx\Console\Command\Init
{
    protected function configure()
    {
        parent::configure();
        $this->setName("db:init");
    }
} // End Init
