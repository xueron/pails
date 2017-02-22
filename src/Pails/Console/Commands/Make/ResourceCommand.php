<?php
namespace Pails\Console\Commands\Make;


use Pails\Console\Command;
use Phalcon\Text;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class ResourceCommand extends Command
{
    protected $name = 'make:resource';

    protected $description = '创建Resource Controller';

    public function handle()
    {
        $name = trim($this->argument('name'));
        $namespace = trim($this->input->getOption('namespace'));
        $extends = trim($this->input->getOption('extends'));
        $routePrefix = trim($this->input->getOption('route-prefix'));

        if ($namespace) {
            $fullNameSpace = 'App\\Http\\Controllers\\' . $namespace;
            $fullPath = $this->getDI()->appPath() . '/Http/Controllers/' . str_replace('\\', '//', $namespace);
        } else {
            $fullNameSpace = 'App\\Http\\Controllers';
            $fullPath = $this->getDI()->appPath() . '/Http/Controllers';
        }


        $className = Text::camelize($name) . 'Controller';
        $fileName = $fullPath . '/' . $className . '.php';
        if (file_exists($fileName)) {
            throw new \LogicException("文件 $fileName 已经存在");
        }

        $stub = @file_get_contents(__DIR__ . '/stubs/resource.stub');
        $stub = str_replace('DummyNamespace', $fullNameSpace, $stub);
        $stub = str_replace('DummyClass', $className, $stub);
        $stub = str_replace('DummyExtends', $extends, $stub);
        $stub = str_replace('dummyPrefix', $routePrefix, $stub);
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
            ['name', InputArgument::REQUIRED, 'Controller名称（类名，不含 \'Controller\'）'],
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
            ['namespace', null, InputOption::VALUE_OPTIONAL, '命名空间，不含 \'App\\Http\\Controllers\'', ''],
            ['extends', null, InputOption::VALUE_OPTIONAL, '继承自', 'ControllerBase'],
            ['route-prefix', null, InputOption::VALUE_OPTIONAL, '路由前缀，如  \'/api/orders\'', ''],
        ];
    }
}
