<?php
/**
 * ExecuteTrait.php
 *
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
        $env = $this->di->environment();
        if ($input->hasOption('env')) {
            $env = $input->getOption('env');
        }

        putenv("APP_ENV=$env");
        if ($this->getDefinition()->hasOption('environment')) {
            $input->setOption('environment', $env);
        }

        if ($this->getDefinition()->hasOption('configuration')) {
            $input->setOption('configuration', $this->di->configPath() . '/database.yml');
        }

        parent::execute($input, $output);
    }
}
