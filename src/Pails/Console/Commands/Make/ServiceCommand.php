<?php
namespace Pails\Console\Commands\Make;


use Pails\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class ServiceCommand extends Command
{
    protected $name = 'make:service';

    protected $description = '创建Service';

    public function handle()
    {
        $name = trim($this->argument('name'));

        $alias = trim($this->option('alias'));
        if ($alias && $this->di->has($alias)) {
            throw new \LogicException("服务 $alias 已经存在");
        }

        $className = $name . 'Service';
        $pathName = $this->di->appPath() . '/Services/';
        if (!file_exists($pathName)) {
            @mkdir($pathName, 0755, true);
        }
        $fileName = $pathName . $className . '.php';
        if (file_exists($fileName)) {
            throw new \LogicException("文件 $fileName 已经存在");
        }

        $stub = @file_get_contents(__DIR__ . '/stubs/service.stub');
        $stub = str_replace('DummyClass', $className, $stub);
        @file_put_contents($fileName, $stub);

        if ($alias) {
            // rewrite services.php config
            $services = (array)$this->di->getConfig('services', null, []);
            $services[$alias] = 'App\\Services\\' . $className;
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
