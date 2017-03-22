<?php
/**
 * ExecuteTrait.php
 */
namespace Pails\Console\Commands\Db;

use Pails\InjectableTrait;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

trait ExecuteTrait
{
    use InjectableTrait;

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // set env through argument --env
        if ($input->getOption('env')) {
            $env = $input->getOption('env');
        } else {
            $env = $this->di->environment();
        }

        //
        if ($this->getDefinition()->hasOption('environment')) {
            if (!($phinxEnv = $input->getOption('environment'))) {
                $input->setOption('environment', $env);
            } else {
                $env = $phinxEnv;
            }
        }
        putenv("APP_ENV=$env");

        if ($this->getDefinition()->hasOption('configuration')) {
            $input->setOption('configuration', $this->di->configPath() . '/database.yml');
        }
        parent::execute($input, $output);
    }
}
