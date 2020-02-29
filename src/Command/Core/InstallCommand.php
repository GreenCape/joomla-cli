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
use GreenCape\JoomlaCLI\DataSource;
use GreenCape\JoomlaCLI\Driver\Version;
use GreenCape\JoomlaCLI\Settings;
use GreenCape\JoomlaCLI\Utility\Expander;
use League\Flysystem\FileNotFoundException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * The Core Install command allows the installation of Joomla! from the command line.
 *
 * @since       Class available since Release __DEPLOY_VERSION__
 */
class InstallCommand extends Command
{
    const NOT_AVAILABLE = 'not available';

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
            ->addArgument(
                'version',
                InputArgument::OPTIONAL,
                'The Joomla! version to install. Ignored, when an installable Joomla version is found at the base path',
                'latest'
            )
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
                InputOption::VALUE_OPTIONAL,
                'The admin email address',
                'admin@localhost'
            )
            ->addOption(
                'db-type',
                't',
                InputOption::VALUE_OPTIONAL,
                'The database type'
            )
            ->addOption(
                'database',
                'd',
                InputOption::VALUE_OPTIONAL,
                /** @lang text */ 'The database connection. Format  <user>:<pass>@<host>:<port>/<database>'
            )
            ->addOption(
                'root',
                'r',
                InputOption::VALUE_OPTIONAL,
                'The database root password'
            )
            ->addOption(
                'prefix',
                'p',
                InputOption::VALUE_OPTIONAL,
                'The table prefix'
            )
            ->addOption(
                'sample',
                's',
                InputOption::VALUE_OPTIONAL,
                'Sample data to be loaded'
            )
        ;
    }

    /**
     * Execute the install command
     *
     * If there already is a Joomla! installation at the base path, nothing happens, so this command is
     * idempotent in this regard.
     *
     * If no Joomla! sources are found at the base path, the version provided with the command is downloaded
     * (defaults to latest stable).
     *
     * If Joomla! sources are present at the base path, the version argument (if given) is ignored.
     *
     * If the Joomla! source does not have an installation directory, the command stops with an error.
     *
     * @param  InputInterface   $input   An InputInterface instance
     * @param  OutputInterface  $output  An OutputInterface instance
     *
     * @return  integer  0 if everything went fine, 1 on error
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->handleBasePath($input, $output);
        $this->ensureJoomlaIsPresent($input->getArgument('version'), $this->basePath, $output);

        if ($this->joomlaFilesystem->has('configuration.php')) {
            $output->writeln('Joomla is already installed in ' . $this->basePath);

            return 0;
        }

        if (!$this->joomlaFilesystem->has('installation/index.php')) {
            $output->writeln('No installation directory found in ' . $this->basePath);

            return 1;
        }

        $output->writeln('Initialising Joomla! environment', OutputInterface::VERBOSITY_DEBUG);
        $this->setupEnvironment('installation', $input, $output);

        $output->writeln('Getting default settings', OutputInterface::VERBOSITY_DEBUG);
        $settings    = new Settings('Joomla');
        $defaultDir  = dirname(__DIR__, 3) . '/build/joomla';
        $environment = $settings->environment($defaultDir . '/default.xml', $defaultDir);

        $output->writeln('Getting settings from environment variables', OutputInterface::VERBOSITY_DEBUG);
        /** @todo Get environment settings from ENV */

        $output->writeln('Getting settings from command options', OutputInterface::VERBOSITY_DEBUG);
        [$user, $pass] = explode(':', $input->getOption('admin'));
        $email = $input->getOption('email');

        if ($input->getOption('db-type') > '') {
            $environment['database']['driver'] = $input->getOption('db-type');

            if (in_array($environment['database']['driver'], ['mysqli', 'pdomysql'])) {
                $environment['database']['engine'] = 'mysql';
            } else {
                $environment['database']['engine'] = $environment['database']['driver'];
            }
        }

        if ($input->getOption('database') > '') {
            $database                        = new DataSource($input->getOption('database'));
            $environment['database']['user'] = $database->getUser();
            $environment['database']['pass'] = $database->getPass();
            $environment['database']['host'] = $database->getHost();
            $environment['database']['port'] = $database->getPort();
            $environment['database']['name'] = $database->getBase();
        }

        $engine = $environment['database']['engine'];

        $output->writeln('Building database seed queries', OutputInterface::VERBOSITY_DEBUG);
        $sql = $this->joomla->getDatabaseCreationQuery($engine);
        $sql .= $this->joomla->getDatabaseSeed($engine);

        if ($input->getOption('sample') > '') {
            $sql .= $this->joomla->getDatabaseSeed($engine, $input->getOption('sample'));
        }

        $sql .= $this->joomla->getRootAccountCreationQuery($engine, $user, $pass, $email);

        $sql = (new Expander())->expand($sql, ['environment' => $environment]);
        $sql = str_replace('#__', $environment['database']['prefix'], $sql);

        file_put_contents('init.sql', $sql);

        #$output->writeln(print_r($environment, true));
        $output->writeln('Installation failed due to unknown reason.');

        return 1;
    }

    /**
     * @param  string           $requestedVersion
     * @param  string           $path
     * @param  OutputInterface  $output
     *
     * @throws \Exception
     */
    private function ensureJoomlaIsPresent(string $requestedVersion, string $path, OutputInterface $output): void
    {
        $output->writeln('Looking for Joomla! in ' . $path, OutputInterface::VERBOSITY_DEBUG);

        $joomlaVersion = $this->joomlaVersion($path);

        if ($joomlaVersion === self::NOT_AVAILABLE) {
            $output->writeln('No Joomla! found in ' . $path . ', downloading Joomla! ' . $requestedVersion,
                OutputInterface::VERBOSITY_VERBOSE);
            $this->download($requestedVersion, $path, $output);
        } else {
            $output->writeln('Found Joomla! ' . $joomlaVersion . ' in ' . $path,
                OutputInterface::VERBOSITY_VERBOSE);
        }
    }

    /**
     * Checks whether Joomla! is present in the given directory
     *
     * @param $dir
     *
     * @return string
     */
    private function joomlaVersion($dir): string
    {
        try {
            $version = new Version($this->joomlaFilesystem);

            return $version->getShortVersion();
        } catch (FileNotFoundException $exception) {
            return self::NOT_AVAILABLE;
        }
    }

    /**
     * @param  string           $version
     * @param  string           $path
     * @param  OutputInterface  $output
     *
     * @throws \Exception
     */
    private function download(string $version, string $path, OutputInterface $output): void
    {
        $download = new DownloadCommand();
        $download->run(new StringInput(" --basepath={$path} {$version}"), $output);
    }
}
