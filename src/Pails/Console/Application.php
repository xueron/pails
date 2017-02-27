<?php
namespace Pails\Console;

use Pails\ApplicationInterface;
use Pails\Console\Commands;
use Pails\Container;
use Pails\ContainerInterface;
use Phalcon\Di;
use Phalcon\Di\InjectionAwareInterface;
use Phalcon\DiInterface;
use Phalcon\Events\EventsAwareInterface;
use Phalcon\Events\ManagerInterface;
use Symfony\Component\Console\Application as ApplicationBase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Pails console application.
 *
 */
abstract class Application extends ApplicationBase implements InjectionAwareInterface, ApplicationInterface, EventsAwareInterface
{
    /**
     * @var Container
     */
    protected $di;

    /**
     * @var ManagerInterface
     */
    protected $eventsManager;

    /**
     * The output from the previous command.
     *
     * @var \Symfony\Component\Console\Output\BufferedOutput
     */
    protected $lastOutput;

    /**
     * Service Providers want to be injected
     *
     * @var array
     */
    protected $providers = [

    ];

    /**
     * 应用创建的命令
     *
     * @var array
     */
    protected $commands = [

    ];

    /**
     * Pails 内建的命令
     *
     * @var array
     */
    protected $pailsCommands = [
        Commands\Cache\ClearCommand::class,
        Commands\Mns\CreateQueueCommand::class,
        Commands\Mns\CreateTopicCommand::class,
        Commands\Mns\DeleteQueueCommand::class,
        Commands\Mns\DeleteTopicCommand::class,
        Commands\Mns\ListQueueCommand::class,
        Commands\Mns\ListTopicCommand::class,
        Commands\Mns\ListSubscriptionsCommand::class,
        Commands\Model\ClearCommand::class,
        Commands\Model\ClearMetaCommand::class,
        Commands\Model\ListCommand::class,
        Commands\Model\ShowCommand::class,
        Commands\Route\ListCommand::class,
        Commands\Route\ClearCommand::class,
        Commands\View\ClearCommand::class,
        Commands\View\ClearVoltCommand::class,
        Commands\Make\CommandCommand::class,
        Commands\Make\ModelCommand::class,
        Commands\Make\ControllerCommand::class,
        Commands\Make\ResourceCommand::class,
        Commands\Make\ServiceCommand::class,
        Commands\Make\ProviderCommand::class,
        Commands\Make\ValidatorCommand::class,

    ];

    /**
     * Class Constructor.
     *
     * Initialize the Pails console application.
     *
     * @param ContainerInterface|DiInterface $di
     * @internal param string $version The Application Version
     */
    public function __construct(ContainerInterface $di = null)
    {
        // 注入DI
        if ($di) {
            $this->setDI($di);
        } else {
            $this->setDI(Di::getDefault());
        }

        // 注入事件管理器
        $this->eventsManager = $this->di->getEventsManager();

        parent::__construct('Pails', $this->di->version());

        // For Phinx, set configuration file by default
        $this->getDefinition()->addOption(new InputOption('--configuration', '-c', InputOption::VALUE_REQUIRED, 'The configuration file to load'));
        array_push($_SERVER['argv'], '--configuration=config/database.yml');

        // Phinx commands wraps
        $this->addCommands(array(
            new Commands\Db\InitCommand(),
            new Commands\Db\BreakpointCommand(),
            new Commands\Db\CreateCommand(),
            new Commands\Db\MigrateCommand(),
            new Commands\Db\RollbackCommand(),
            new Commands\Db\SeedCreateCommand(),
            new Commands\Db\SeedRunCommand(),
            new Commands\Db\StatusCommand()
        ));

        // Pails commands
        $this->resolveCommands($this->pailsCommands);
    }

    /**
     * Runs the current application.
     *
     * @param InputInterface $input An Input instance
     * @param OutputInterface $output An Output instance
     * @return integer 0 if everything went fine, or an error code
     */
    public function doRun(InputInterface $input, OutputInterface $output)
    {
        // always show the version information except when the user invokes the help
        // command as that already does it
        if (false === $input->hasParameterOption(array('--help', '-h')) && null !== $input->getFirstArgument()) {
            $output->writeln($this->getLongVersion());
            $output->writeln('');
        }

        return parent::doRun($input, $output);
    }

    /**
     * Get the default input definitions for the applications.
     *
     * This is used to add the --env option to every available command.
     *
     * @return \Symfony\Component\Console\Input\InputDefinition
     */
    protected function getDefaultInputDefinition()
    {
        $definition = parent::getDefaultInputDefinition();

        $definition->addOption($this->getEnvironmentOption());

        return $definition;
    }

    /**
     * Get the global environment option for the definition.
     *
     * @return \Symfony\Component\Console\Input\InputOption
     */
    protected function getEnvironmentOption()
    {
        $message = 'The environment the command should run under.';

        return new InputOption('--env', null, InputOption::VALUE_OPTIONAL, $message);
    }

    /**
     * Run an console command by name.
     *
     * @param  string  $command
     * @param  array  $parameters
     * @return int
     */
    public function call($command, array $parameters = [])
    {
        array_unshift($parameters, $command);

        $this->lastOutput = new BufferedOutput;

        $this->setCatchExceptions(false);

        $result = $this->run(new ArrayInput($parameters), $this->lastOutput);

        $this->setCatchExceptions(true);

        return $result;
    }

    /**
     * @return \Phalcon\DiInterface
     */
    public function getDI()
    {
        return $this->di;
    }

    /**
     * Sets the dependency injector
     *
     * @param mixed $dependencyInjector
     */
    public function setDI(DiInterface $dependencyInjector)
    {
        $this->di = $dependencyInjector;
    }

    /**
     * register services
     */
    public function boot()
    {
        $this->di->registerServices($this->providers);

        // register services from services.php
        $providers = (array)$this->di->getConfig('providers', null, []);
        $this->di->registerServices(array_values($providers));

        // register services from services.php
        $services = (array)$this->di->getConfig('services', null, []);
        foreach ($services as $name => $class) {
            $this->getDI()->setShared($name, $class);
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function init()
    {
        // load from config file
        $commands = array_values((array)$this->di->getConfig('commands', null, []));
        $this->resolveCommands($commands);

        // load from Application.php
        $this->resolveCommands($this->commands);

        return $this;
    }

    /**
     * @return mixed
     */
    public function handle()
    {
        $result = $this->run();

        return $result;
    }

    /**
     * Get the output for the last run command.
     *
     * @return string
     */
    public function output()
    {
        return $this->lastOutput ? $this->lastOutput->fetch() : '';
    }

    /**
     * Add a command, resolving through the application. 通过DI的自动注入功能，注入DI和事件管理器
     *
     * @param  string  $command
     * @return \Symfony\Component\Console\Command\Command
     */
    public function resolve($command)
    {
        $commandInstance = $this->di->get($command);
        $commandInstance->setEventsManager($this->di->getEventsManager());
        return $this->add($commandInstance);
    }

    /**
     * Resolve an array of commands through the application.
     *
     * @param  array|mixed  $commands
     * @return $this
     */
    public function resolveCommands($commands)
    {
        $commands = is_array($commands) ? $commands : func_get_args();

        foreach ($commands as $command) {
            $this->resolve($command);
        }

        return $this;
    }

    /**
     * @param ManagerInterface $eventsManager
     * @return $this
     */
    public function setEventsManager(ManagerInterface $eventsManager)
    {
        $this->eventsManager = $eventsManager;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getEventsManager()
    {
        return $this->eventsManager;
    }
}
