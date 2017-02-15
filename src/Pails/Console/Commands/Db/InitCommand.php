<?php
namespace Pails\Console\Commands\Db;


use Phinx\Console\Command\Init;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class InitCommand extends Init
{
    protected function configure()
    {
        $this->setName('db:init')
            ->setDescription('初始化数据库迁移')
            ->setHelp(sprintf(
                '%sInitializes the application for Phinx%s',
                PHP_EOL,
                PHP_EOL
            ));
    }



    /**
     * Initializes the application.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // get the migration path from the config
        $path = getcwd() . DIRECTORY_SEPARATOR . 'config';
        $path = realpath($path);
        if (!is_writable($path)) {
            throw new \InvalidArgumentException(sprintf(
                'The directory "%s" is not writable',
                $path
            ));
        }

        // Compute the file path
        $fileName = 'database.yml'; // TODO - maybe in the future we allow custom config names.
        $filePath = $path . DIRECTORY_SEPARATOR . $fileName;

        if (file_exists($filePath)) {
            throw new \InvalidArgumentException(sprintf(
                'The file "%s" already exists',
                $filePath
            ));
        }

        // load the config template
        $contents = file_get_contents(__DIR__ . '/database.yml');
        if (false === file_put_contents($filePath, $contents)) {
            throw new \RuntimeException(sprintf(
                'The file "%s" could not be written to',
                $path
            ));
        }

        $output->writeln('<info>created</info> .' . str_replace(getcwd(), '', $filePath));
    }
} // End Init
