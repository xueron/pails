<?php
namespace Pails\Console;

use Pails\ApplicationInterface;
use Pails\InjectableTrait;
use Phalcon\Di\InjectionAwareInterface;
use Phalcon\Events\EventsAwareInterface;
use Symfony\Component\Console\Application as ApplicationBase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Pails console application.
 */
abstract class Application extends ApplicationBase implements InjectionAwareInterface, ApplicationInterface, EventsAwareInterface
{
    use InjectableTrait;

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
        Commands\Db\BreakpointCommand::class,
        Commands\Db\CreateCommand::class,
        Commands\Db\InitCommand::class,
        Commands\Db\MigrateCommand::class,
        Commands\Db\RollbackCommand::class,
        Commands\Db\SeedCreateCommand::class,
        Commands\Db\SeedRunCommand::class,
        Commands\Db\StatusCommand::class,
        Commands\Db\TestCommand::class,

        Commands\Cache\ClearCommand::class,

        Commands\Mns\CreateQueueCommand::class,
        Commands\Mns\CreateTopicCommand::class,
        Commands\Mns\DeleteQueueCommand::class,
        Commands\Mns\DeleteTopicCommand::class,
        Commands\Mns\ListQueueCommand::class,
        Commands\Mns\ListTopicCommand::class,
        Commands\Mns\ListSubscriptionsCommand::class,
        Commands\Mns\SubscribeCommand::class,
        Commands\Mns\UnSubscribeCommand::class,
        Commands\Mns\UpdateTopicCommand::class,
        Commands\Mns\UpdateQueueCommand::class,
        Commands\Mns\UpdateSubscriptionCommand::class,
        Commands\Mns\PublishTopicCommand::class,
        Commands\Mns\SendQueueCommand::class,

        Commands\Model\ClearCommand::class,
        Commands\Model\ClearMetaCommand::class,
        Commands\Model\ListCommand::class,
        Commands\Model\ShowCommand::class,

        Commands\Route\ListCommand::class,
        Commands\Route\ClearCommand::class,

        Commands\View\ClearCommand::class,
        Commands\View\ClearVoltCommand::class,

        Commands\Make\CommandCommand::class,
        Commands\Make\ConfigCommand::class,
        Commands\Make\ModelCommand::class,
        Commands\Make\ControllerCommand::class,
        Commands\Make\ResourceCommand::class,
        Commands\Make\ServiceCommand::class,
        Commands\Make\ProviderCommand::class,
        Commands\Make\ValidatorCommand::class,
        Commands\Make\WorkerCommand::class,
        Commands\Make\ListenerCommand::class,

        Commands\Queue\ListenCommand::class,
    ];

    /**
     * Class Constructor.
     *
     * Initialize the Pails console application.
     */
    public function __construct()
    {
        parent::__construct('Pails', $this->di->version());

        // Pails commands
        $this->resolveCommands($this->pailsCommands);
    }

    /**
     * Runs the current application.
     *
     * @param InputInterface  $input  An Input instance
     * @param OutputInterface $output An Output instance
     *
     * @return int 0 if everything went fine, or an error code
     */
    public function doRun(InputInterface $input, OutputInterface $output)
    {
        // always show the version information except when the user invokes the help
        // command as that already does it
        if (false === $input->hasParameterOption(['--help', '-h']) && null !== $input->getFirstArgument()) {
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

        return new InputOption('--env', null, InputOption::VALUE_OPTIONAL, $message, 'development');
    }

    /**
     * Run an console command by name.
     *
     * @param string $command
     * @param array  $parameters
     *
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
     * register services
     */
    public function boot()
    {
        $this->di->registerServices($this->providers);

        // register services from providers.php
        $providers = (array) $this->di->getConfig('providers', null, []);
        $this->di->registerServices(array_values($providers));

        // register services from services.php
        $services = (array) $this->di->getConfig('services', null, []);
        foreach ($services as $name => $class) {
            $this->getDI()->setShared($name, $class);
        }

        // register listeners from listeners.php
        $listeners = (array) $this->di->getConfig('listeners', null, []);
        foreach ($listeners as $event => $listener) {
            $this->eventsManager->attach($event, $this->di->getShared($listener));
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function init()
    {
        // load from config file
        $commands = array_values((array) $this->di->getConfig('commands', null, []));
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
     * @param string $command
     *
     * @return \Symfony\Component\Console\Command\Command
     */
    public function resolve($command)
    {
        $commandInstance = $this->di->get($command);

        return $this->add($commandInstance);
    }

    /**
     * Resolve an array of commands through the application.
     *
     * @param array|mixed $commands
     *
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
}
