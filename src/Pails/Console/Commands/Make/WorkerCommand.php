<?php
namespace Pails\Console\Commands\Make;


use Pails\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class WorkerCommand extends Command
{
    protected $name = 'make:worker';

    protected $description = '创建Worker';

    public function handle()
    {
        $name = trim($this->argument('name'));

        $queue = trim($this->option('queue'));
        $alias = 'queue:' . $queue;
        if ($alias && $this->getDI()->has($alias)) {
            throw new \LogicException("服务 $alias 已经存在");
        }

        $className = $name . 'Worker';
        $pathName = $this->getDI()->appPath() . '/Workers/';
        if (!file_exists($pathName)) {
            @mkdir($pathName, 0755, true);
        }
        $fileName = $pathName . $className . '.php';
        if (file_exists($fileName)) {
            throw new \LogicException("文件 $fileName 已经存在");
        }

        $stub = @file_get_contents(__DIR__ . '/stubs/worker.stub');
        $stub = str_replace('DummyClass', $className, $stub);
        @file_put_contents($fileName, $stub);

        if ($alias) {
            // rewrite services.php config
            $services = (array)$this->di->getConfig('services', null, []);
            $services[$alias] = 'App\\Workers\\' . $className;
            @file_put_contents($this->di->configPath() . '/services.php', "<?php return " . var_export($services, true) . ";");
        }

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
            ['name', InputArgument::REQUIRED, 'Worker的名称（类名，不含 \'Worker\' 后缀）'],
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
            ['queue', null, InputOption::VALUE_REQUIRED, '监听队列的名称', null],
        ];
    }
}
