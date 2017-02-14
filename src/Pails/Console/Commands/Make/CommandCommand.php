<?php
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
        $command = trim($this->option('command'));
        if ($this->getApplication()->has($command)) {
            throw new \LogicException("命令 $command 已经存在");
        }

        $className = Text::camelize($name) . 'Command';
        $fileName = $this->getDI()->appPath() . '/Console/Commands/' . $className . '.php';
        if (file_exists($fileName)) {
            throw new \LogicException("文件 $fileName 已经存在");
        }

        // create command file
        $stub = @file_get_contents(__DIR__ . '/stubs/command.stub');
        $stub = str_replace('DummyClass', $className, $stub);
        $stub = str_replace('dummy:command', $command, $stub);
        @file_put_contents($fileName, $stub);

        // rewrite commands.php config
        $commands = (array)$this->getDI()->getConfig('commands', null, []);
        $commands[$command] = 'App\\Console\\Commands\\' . $className;
        @file_put_contents($this->getDI()->configPath() . '/commands.php', "<?php return " . var_export($commands, true) . ";");

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
