<?php
namespace Pails\Console\Commands\Make;


use Pails\Console\Command;
use Phalcon\Text;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class ModelCommand extends Command
{
    protected $name = 'make:model';

    protected $description = '创建Model';

    public function handle()
    {
        $name = trim($this->argument('name'));
        $stub = @file_get_contents(__DIR__ . '/stubs/model.stub');

        $className = Text::camelize($name);
        $fileName = $this->getDI()->appPath() . '/Models/' . $className . '.php';

        $stub = str_replace('DummyClass', $className, $stub);

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
            ['name', InputArgument::REQUIRED, 'Model的名称（类名）'],
        ];
    }
}
