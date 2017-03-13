<?php
namespace Pails\Console\Commands\Make;

use Pails\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class ProviderCommand extends Command
{
    protected $name = 'make:provider';

    protected $description = '创建Provider';

    public function handle()
    {
        $name = trim($this->argument('name'));

        $alias = trim($this->option('alias'));
        if ($alias && $this->di->has($alias)) {
            throw new \LogicException("服务 $alias 已经存在");
        }

        $className = $name . 'Provider';
        $pathName = $this->di->appPath() . '/Providers/';
        if (!file_exists($pathName)) {
            @mkdir($pathName, 0755, true);
        }
        $fileName = $pathName . $className . '.php';
        if (file_exists($fileName)) {
            throw new \LogicException("文件 $fileName 已经存在");
        }

        $stub = @file_get_contents(__DIR__ . '/stubs/provider.stub');
        $stub = str_replace('DummyClass', $className, $stub);
        $stub = str_replace('dummyServiceName', $alias, $stub);
        @file_put_contents($fileName, $stub);

        // rewrite services.php config
        $providers = (array) $this->di->getConfig('providers', null, []);
        $providers[$alias] = 'App\\Providers\\' . $className;
        @file_put_contents($this->di->configPath() . '/providers.php', '<?php return ' . var_export($providers, true) . ';');

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
            ['name', InputArgument::REQUIRED, 'Provider的名称（类名，不含Provider后缀）'],
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
            ['alias', null, InputOption::VALUE_REQUIRED, '服务的名称', null],
        ];
    }
}
