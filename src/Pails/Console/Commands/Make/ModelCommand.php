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

        $className = $name;
        $pathName = $this->di->appPath() . '/Models/';
        if (!file_exists($pathName)) {
            @mkdir($pathName, 0755, true);
        }
        $fileName = $pathName . $className . '.php';
        if (file_exists($fileName)) {
            throw new \LogicException("文件 $fileName 已经存在");
        }

        $stub = @file_get_contents(__DIR__ . '/stubs/model.stub');
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
