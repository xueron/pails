<?php

namespace Pails\Console\Commands\Make;

use Pails\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class ListenerCommand extends Command
{
    protected $name = 'make:listener';

    protected $description = '创建Listener. Listener 是Phalcon事件的监听程序';

    public function handle()
    {
        //
        $name = trim($this->argument('name'));
        $event = trim($this->option('event'));
        $pri = intval(trim($this->option('pri')));
        if ($pri < 0) {
            $pri = 0;
        }

        // create file
        $className = $name . 'Listener';
        $pathName = $this->di->appPath() . '/Listeners/';
        if (!file_exists($pathName)) {
            @mkdir($pathName, 0755, true);
        }
        $fileName = $pathName . $className . '.php';
        if (file_exists($fileName)) {
            throw new \LogicException("文件 $fileName 已经存在");
        }

        $stub = @file_get_contents(__DIR__ . '/stubs/listener.stub');
        $stub = str_replace('DummyClass', $className, $stub);
        @file_put_contents($fileName, $stub);

        // rewrite listeners.php config
        $listeners = $this->di->getConfig('listeners', null);
        if ($listeners && count($listeners)) {
            $listeners = $listeners->toArray();
        } else {
            $listeners = [];
        }
        $listeners[] = ['class' => 'App\\Listeners\\' . $className, 'event' => $event, 'pri' => $pri];
        @file_put_contents($this->di->configPath() . '/listeners.php', '<?php return ' . var_export($listeners, true) . ';');

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
            ['name', InputArgument::REQUIRED, '监听器的名称（类名）'],
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
            ['event', null, InputOption::VALUE_REQUIRED, '事件的名称（如 db，或者 db:beforeQuery）', null],
            ['pri', null, InputOption::VALUE_OPTIONAL, '处理器的优先级，整数，越大优先级越高', 10],
        ];
    }
}
