<?php
/**
 * StatusCommand.php
 *
 */
namespace Pails\Console\Commands\Db;

use Phinx\Console\Command\Test;

class TestCommand extends Test
{
    use ExecuteTrait;

    protected function configure()
    {
        parent::configure();

        $this->setName("db:test");
        $this->setDescription("测试数据库配置");
    }
}
