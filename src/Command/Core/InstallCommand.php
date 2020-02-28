<?php
/**
 * GreenCape Joomla Command Line Interface
 *
 * MIT License
 *
 * Copyright (c) 2012-2019, Niels Braczek <nbraczek@bsds.de>. All rights reserved.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated
 * documentation files (the "Software"), to deal in the Software without restriction, including without limitation
 * the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and
 * to permit persons to whom the Software is furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all copies or substantial portions
 * of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO
 * THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT,
 * TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 *
 * @author          Niels Braczek <nbraczek@bsds.de>
 * @copyright   (C) 2012-2019 GreenCape, Niels Braczek <nbraczek@bsds.de>
 * @license         http://opensource.org/licenses/MIT The MIT license (MIT)
 * @link            http://greencape.github.io
 * @since           File available since Release __DEPLOY_VERSION__
 */

namespace GreenCape\JoomlaCLI\Command\Core;

use GreenCape\JoomlaCLI\Command;
use GreenCape\JoomlaCLI\Settings;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * The Core Install command allows the installation of Joomla! from the command line.
 *
 * @since       Class available since Release __DEPLOY_VERSION__
 */
class InstallCommand extends Command
{
    /**
     * Configure the options for the install command
     *
     * @return  void
     */
    protected function configure(): void
    {
        $this
            ->setName('core:install')
            ->setDescription('Installs Joomla!')
            ->addOption(
                'admin',
                'a',
                InputOption::VALUE_REQUIRED,
                'The admin user name and password, separated by colon',
                'admin:admin'
            )
            ->addOption(
                'email',
                'e',
                InputOption::VALUE_REQUIRED,
                'The admin email address',
                'admin@localhost'
            )
            ->addOption(
                'db-type',
                't',
                InputOption::VALUE_REQUIRED,
                'The database type',
                'mysqli'
            )
            ->addOption(
                'database',
                'd',
                InputOption::VALUE_REQUIRED,
                /** @lang text */ 'The database connection. Format  <user>:<pass>@<host>:<port>/<database>',
                'sqladmin:sqladmin@localhost:3306/database'
            )
            ->addOption(
                'root',
                'r',
                InputOption::VALUE_REQUIRED,
                'The database root password',
                'root'
            )
            ->addOption(
                'prefix',
                'p',
                InputOption::VALUE_REQUIRED,
                'The table prefix',
                'jos_'
            )
        ;
    }

    /**
     * Execute the install command
     *
     * @param  InputInterface   $input   An InputInterface instance
     * @param  OutputInterface  $output  An OutputInterface instance
     *
     * @return  integer  0 if everything went fine, 1 on error
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $dir = dirname(__DIR__, 3) . '/build/joomla';

        $settings    = new Settings('Joomla');
        $environment = $settings->environment($dir . '/default.xml', $dir);

        $this->setupEnvironment('installation', $input, $output);

        $output->writeln(print_r($environment, true));
        $output->writeln('Installation failed due to unknown reason.');

        return 1;
    }
}
