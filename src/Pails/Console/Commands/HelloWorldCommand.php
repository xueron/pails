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

    protected $description = 'This is a demo command';

    public function handle()
    {
        $this->comment(PHP_EOL.'Hello World!'.PHP_EOL);
    }
}
