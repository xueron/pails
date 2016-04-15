<?php
/**
 * Pails
 *
 * (The MIT license)
 * Copyright (c) 2016 Xueron Nee
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated * documentation files (the "Software"), to
 * deal in the Software without restriction, including without limitation the
 * rights to use, copy, modify, merge, publish, distribute, sublicense, and/or
 * sell copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS
 * IN THE SOFTWARE.
 *
 * @package    Pails
 * @subpackage Pails\Console
 */
namespace Pails\Console;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Pails\Console\Command as PailsCommand;
use Phinx\Console\Command as PhinxCommand;

/**
 * Pails console application.
 *
 */
class PailsApplication extends Application
{
    /**
     * Class Constructor.
     *
     * Initialize the Pails console application.
     *
     * @param string $version The Application Version
     */
    public function __construct($version = '0.0.1')
    {
        parent::__construct('Pails', $version);

        // For Phinx, set configuration file by default
        $this->getDefinition()->addOption(new InputOption('--configuration', '-c', InputOption::VALUE_REQUIRED, 'The configuration file to load'));
        array_push($_SERVER['argv'], '--configuration=config/database.yml');

        //
        $this->addCommands(array(
            // Phinx migrations
            new PhinxCommand\Create(),
            new PhinxCommand\Migrate(),
            new PhinxCommand\Rollback(),
            new PhinxCommand\Status(),
            new PhinxCommand\Test(),
            new PhinxCommand\SeedCreate(),
            new PhinxCommand\SeedRun(),

            // Pails
            new PailsCommand\Create()
        ));
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
}
