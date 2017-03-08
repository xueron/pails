<?php
namespace Pails\Console\Commands\Make;

use Pails\Console\Command;

class ConfigCommand extends Command
{
    protected $signature = 'make:config
        {name : 配置文件的名称，小写字母}';

    protected $description = '创建配置文件模板';

    public function handle()
    {
        $name = trim($this->argument('name'));

        $fileName = $this->di->configPath() . '/' . $name . '.php';
        if (file_exists($fileName)) {
            throw new \LogicException("文件 $fileName 已经存在");
        }

        $stub = @file_get_contents(__DIR__ . '/stubs/config.stub');
        @file_put_contents($fileName, $stub);

        $this->info("$name created at $fileName");
    }
}
