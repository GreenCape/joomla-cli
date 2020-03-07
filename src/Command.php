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
 * @since           File available since Release 0.1.0
 */

namespace GreenCape\JoomlaCLI;

use Exception;
use GreenCape\JoomlaCLI\Driver\Environment;
use GreenCape\JoomlaCLI\Driver\Factory;
use GreenCape\JoomlaCLI\Driver\JoomlaDriver;
use League\Flysystem\Adapter\Local;
use League\Flysystem\FileNotFoundException;
use League\Flysystem\Filesystem;
use Symfony\Component\Console\Command\Command as BaseCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * The abstract command provides common methods for most JoomlaCLI commands.
 *
 * @since  Class available since Release 0.1.0
 */
abstract class Command extends BaseCommand
{
    /**
     * @var JoomlaDriver
     */
    protected $joomla;

    /**
     * @var Environment
     */
    protected $environment;

    /**
     * @var string
     */
    protected $basePath;

    /**
     * @var Filesystem
     */
    protected $joomlaFilesystem;

    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * @var InputInterface
     */
    protected $input;

    use CommonOptions, FilesystemMethods;

    /**
     * Constructor.
     *
     * @param  string  $name  The name of the command
     */
    public function __construct($name = null)
    {
        parent::__construct($name);
        $this->addGlobalOptions();
    }

    /**
     * Add options common to all commands
     *
     * @return  void
     */
    protected function addGlobalOptions(): void
    {
        $this
            ->addBasePathOption()
            ->addEnvironmentOption()
        ;
    }

    /**
     * Initializes the command after the input has been bound and before the input
     * is validated.
     *
     * This is mainly useful when a lot of commands extends one main command
     * where some things need to be initialized based on the input arguments and options.
     *
     * @param  InputInterface   $input
     * @param  OutputInterface  $output
     *
     * @see InputInterface::bind()
     * @see InputInterface::validate()
     */
    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->input       = $input;
        $this->output      = $output;
        $this->basePath    = $input->getOption('basepath');
        $this->environment = new Environment($input->getOption('environment'));
    }

    /**
     * Setup the environment
     *
     * @param  string  $application  The application, eg., 'site' or 'administration'
     *
     * @return  void
     * @throws Exception
     */
    protected function setupEnvironment($application): void
    {
        $this->loadDriver();
        $this->joomla->setupEnvironment($application);
    }

    /**
     * @return void
     * @throws FileNotFoundException
     */
    protected function loadDriver(): void
    {
        $this->joomla = (new Factory)->create($this->joomlaFilesystem);
    }

    /**
     * Read the base path from the options
     *
     * @return  string  The base path
     */
    protected function handleBasePath(): string
    {
        $path                   = realpath($this->input->getOption('basepath'));
        $adapter                = new Local($path);
        $this->joomlaFilesystem = new Filesystem($adapter);

        $this->output->writeln('Joomla! installation expected in ' . $path, OutputInterface::VERBOSITY_DEBUG);

        return $path;
    }

    /**
     * Execute a Shell command
     *
     * Output respects verbosity settings.
     * In passthru mode, the output of the command is sent to the output channel directly.
     * Otherwise, the last line from the result of the command is sent to the output for normal verbosity,
     * or the whole output for increased verbosity.
     *
     * If the `--quiet` option is set, output is suppressed completely, also in passthru mode.
     *
     * @param  string  $command   The command to be executed
     * @param  string  $dir       The directory to execute the command in
     * @param  bool    $passthru  If set to `true`, `passthru()` is used to execute the command instead of `exec()`
     *
     * @return int The return status of the executed command
     */
    protected function exec(string $command, string $dir = '.', bool $passthru = true): int
    {
        $this->output->writeln("Running `{$command}` in `{$dir}`", OutputInterface::VERBOSITY_DEBUG);

        $current = getcwd();

        if ($this->input->getOption('quiet')) {
            ob_start();
        }

        chdir($dir);

        $result = '';

        if ($passthru) {
            passthru($command . ' 2>&1', $result);
        } else {
            $output   = '';
            $lastLine = exec($command . ' 2>&1', $output, $result);
            $this->output->writeln($output, OutputInterface::VERBOSITY_VERBOSE);

            if ($this->output->getVerbosity() < OutputInterface::VERBOSITY_VERBOSE) {
                $this->output->writeln($lastLine, OutputInterface::VERBOSITY_NORMAL);
            }
        }

        chdir($current);

        if ($this->input->getOption('quiet')) {
            ob_end_clean();
        }

        return $result;
    }
}
