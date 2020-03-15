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
use GreenCape\JoomlaCLI\Driver\Factory;
use GreenCape\JoomlaCLI\Driver\JoomlaDriver;
use GreenCape\Manifest\Manifest;
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

    /**
     * @var array
     */
    protected $project;

    /**
     * @var array
     */
    protected $package;

    /**
     * @var array
     */
    protected $verbosity = [
        OutputInterface::VERBOSITY_QUIET        => ' -q',
        OutputInterface::VERBOSITY_NORMAL       => ' -p',
        OutputInterface::VERBOSITY_VERBOSE      => ' -v',
        OutputInterface::VERBOSITY_VERY_VERBOSE => ' -vv',
        OutputInterface::VERBOSITY_DEBUG        => ' -vvv',
    ];

    /**
     * @var string The user as uig:gid
     */
    protected $user;

    /**
     * @var string
     */
    protected $build;

    /**
     * @var string
     */
    protected $tests;

    /**
     * @var string
     */
    protected $bin;

    /**
     * @var string
     */
    protected $unitTests;

    /**
     * @var string
     */
    protected $integrationTests;

    /**
     * @var string
     */
    protected $systemTests;

    /**
     * @var string
     */
    protected $testEnvironments;

    /**
     * @var string
     */
    protected $serverDockyard;

    /**
     * @var string
     */
    protected $versionCache;

    /**
     * @var string
     */
    protected $downloadCache;

    /**
     * @var array
     */
    protected $dist = [];

    /**
     * @var string
     */
    protected $buildTemplates;

    use CommonOptions, FilesystemMethods;

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
        $this->input  = $input;
        $this->output = $output;

        $this->init();
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

        if ($this->output->getVerbosity() === OutputInterface::VERBOSITY_QUIET) {
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

        if ($this->output->getVerbosity() === OutputInterface::VERBOSITY_QUIET) {
            ob_end_clean();
        }

        return $result;
    }

    /**
     * Get the verbosity option for symfony (sub-)commands.
     *
     * Might be overridden in subsequent commands.
     *
     * @return string
     */
    protected function verbosity(): string
    {
        return $this->verbosity[$this->output->getVerbosity()];
    }

    /**
     * @param  string           $commandClass
     * @param  InputInterface   $input
     * @param  OutputInterface  $output
     *
     * @return int
     * @throws Exception
     */
    protected function runCommand(string $commandClass, InputInterface $input, OutputInterface $output): int
    {
        /** @var BaseCommand $command */
        $command = new $commandClass();

        return $command->run($input, $output);
    }

    /**
     * Initialise.
     */
    private function init(): void
    {
        $this->output->writeln('Command: ' . get_class($this), OutputInterface::VERBOSITY_DEBUG);

        $this->initUser();
        $this->initBasePath(getcwd());

        $settings = $this->readProject('project.json');

        $this->initSourcePath();
        $this->initJoomlaPath();
        $this->initLogPath();

        $this->initEnvironment();

        if (empty($settings)) {
            return;
        }

        $this->readManifest($settings['package']['manifest']);

        $this->output->writeln(
            ucfirst($this->package['type']) . " {$this->package['name']} {$this->package['version']}",
            OutputInterface::VERBOSITY_VERBOSE
        );

        $this->initDistPath();
    }

    private function initUser(): void
    {
        $this->user = getmyuid() . ':' . getmygid();
    }

    /**
     * @param  string  $projectFile
     *
     * @return array
     */
    private function readProject(string $projectFile): array
    {
        $filename = $this->base . '/' . $projectFile;

        if (!file_exists($filename)) {
            $this->output->writeln(
                'Project file {$projectFile} not found at base path {$this->base}',
                OutputInterface::VERBOSITY_VERBOSE
            );

            return [];
        }

        $this->output->writeln("Reading project file {$projectFile}", OutputInterface::VERBOSITY_DEBUG);

        $settings = json_decode(file_get_contents($filename), true);

        $this->project = $settings['project'];
        $this->package = $this->readPackage($settings['package']);

        if (empty($this->project['name'])) {
            $this->project['name'] = $this->package['name'];
        }

        $this->output->writeln(
            "Project: {$this->project['name']} {$this->project['version']}",
            OutputInterface::VERBOSITY_VERBOSE
        );

        return $settings;
    }

    /**
     * @param $settings
     *
     * @return mixed
     */
    private function readPackage($settings): array
    {
        $package['name']     = $settings['name'] ?? 'com_' . strtolower(
                preg_replace('~\W+~', '_', $this->project['name'])
            );
        $package['type']     = $settings['type'] ?? 'component';
        $package['manifest'] = $settings['manifest'] ?? 'manifest.xml';
        $package['version']  = $settings['version'] ?? $this->project['version'];

        if (isset($settings['extensions'])) {
            foreach ($settings['extensions'] as $extension) {
                $extension['version']                      = $extension['version'] ?? $package['version'];
                $package['extensions'][$extension['name']] = $extension;
            }
        }

        return $package;
    }

    /**
     * @param $manifestFile
     */
    private function readManifest($manifestFile): void
    {
        if (file_exists($this->source . '/' . $manifestFile)) {
            $this->output->writeln(
                "Reading manifest file {$manifestFile}",
                OutputInterface::VERBOSITY_DEBUG
            );

            $manifest = Manifest::load($this->source . '/' . $manifestFile);

            $this->package['name']   = $manifest->getName();
            $this->package['type']   = $manifest->getType();
            $this->package['target'] = $manifest->getTarget();

            if ($manifest->getType() === 'package') {
                foreach ($manifest->getSection('files')->getStructure() as $extension) {
                    $this->package['extensions'][$extension['@id']]['archive'] = ltrim(
                        ($extension['@base'] ?? '') . '/' . $extension['file'],
                        '/'
                    );
                    $this->package['extensions'][$extension['@id']]['type']    = $extension['@type'];
                }
            }
        } else {
            $this->output->writeln(
                "Manifest file '{$manifestFile}' not found.",
                OutputInterface::VERBOSITY_VERBOSE
            );
        }
    }

    private function initDistPath(): void
    {
        $this->dist['basedir'] = "{$this->base}/dist/{$this->package['name']}-{$this->project['version']}";
    }
}
