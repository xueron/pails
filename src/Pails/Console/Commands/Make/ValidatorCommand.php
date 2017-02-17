<?php
namespace Pails\Console\Commands\Make;


use Pails\Console\Command;
use Phalcon\Text;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class ServiceCommand extends Command
{
    protected $name = 'make:service';

    protected $description = '创建服务';

    public function handle()
    {
        $name = trim($this->argument('name'));

        $alias = trim($this->option('alias'));
        if ($alias && $this->getDI()->has($alias)) {
            throw new \LogicException("服务 $alias 已经存在");
        }

        $className = Text::camelize($name) . 'Service';
        $fileName = $this->getDI()->appPath() . '/Services/' . $className . '.php';
        if (file_exists($fileName)) {
            throw new \LogicException("文件 $fileName 已经存在");
        }

        $stub = @file_get_contents(__DIR__ . '/stubs/service.stub');
        $stub = str_replace('DummyClass', $className, $stub);
        @file_put_contents($fileName, $stub);

        if ($alias) {
            // rewrite services.php config
            $services = (array)$this->getDI()->getConfig('services', null, []);
            $services[$alias] = 'App\\Services\\' . $className;
            @file_put_contents($this->getDI()->configPath() . '/services.php', "<?php return " . var_export($services, true) . ";");
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
            ['name', InputArgument::REQUIRED, '服务的名称（类名）'],
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
            ['alias', null, InputOption::VALUE_OPTIONAL, '服务的别名', null],
        ];
    }
}
