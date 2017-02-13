<?php
/**
 * ClearCommand.php
 *
 */


namespace Pails\Console\Commands\Make;


use Pails\Console\Command;
use Phalcon\Text;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class CommandCommand extends Command
{
    protected $name = 'make:command';

    protected $description = '创建一个命令行工具';

    public function handle()
    {
        $name = trim($this->argument('name'));
        $stub = @file_get_contents(__DIR__ . '/stubs/command.stub');

        $className = Text::camelize($name) . 'Command';
        $fileName = $this->getDI()->appPath() . '/Console/Commands/' . $className . '.php';

        $stub = str_replace('DummyClass', $className, $stub);
        $stub = str_replace('dummy:command', $this->option('command'), $stub);

        @file_put_contents($fileName, $stub);

        $this->info("$name created at $fileName");
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['name', InputArgument::REQUIRED, '工具的名称（类名）'],
        ];
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['command', null, InputOption::VALUE_OPTIONAL, '命令行的名称', 'command:name'],
        ];
    }
}
