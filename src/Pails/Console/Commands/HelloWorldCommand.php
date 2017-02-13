<?php
/**
 * HelloWorldCommand.php
 *
 */
namespace Pails\Console\Commands;

use Pails\Console\Command;

class HelloWorldCommand extends Command
{
    protected $name = 'pails:helloworld';

    protected $description = '这是一个Pails内建的命令行工具的Demo';

    public function handle()
    {
        $this->comment(PHP_EOL.'Hello World!'.PHP_EOL);
    }
}
