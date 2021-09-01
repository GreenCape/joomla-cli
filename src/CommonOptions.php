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

namespace GreenCape\JoomlaCLI;

use GreenCape\JoomlaCLI\Driver\Environment;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Common Options Trait
 *
 * Provides commonly used options for consistency
 *
 * @property InputInterface  $input  Must be provided by class using this trait
 * @property OutputInterface $output Must be provided by class using this trait
 *
 * @since  Class available since Release __DEPLOY_VERSION__
 */
trait CommonOptions
{
    /**
     * @var string
     */
    protected $base;

    /**
     * @var Environment
     */
    protected $environment;

    /**
     * @var string
     */
    protected $joomla;

    /**
     * @var string
     */
    protected $source;

    /**
     * @var string
     */
    protected $logs;

    protected function addEnvironmentOption(): Command
    {
        $this->addOption(
            'environment',
            'e',
            InputOption::VALUE_REQUIRED,
            'The environment definition',
            ''
        );

        return $this;
    }

    protected function initEnvironment(): void
    {
        if ($this->input->hasOption('environment')) {
            $this->environment = new Environment($this->input->getOption('environment'));
        }
    }

    protected function addBasePathOption(): Command
    {
        $this->addOption(
            'basepath',
            'b',
            InputOption::VALUE_OPTIONAL,
            'The path of the project root, defaults to current directory'
        );

        return $this;
    }

    protected function initBasePath($defaultPath): void
    {
        $setBy      = 'default';
        $this->base = realpath($defaultPath);

        if ($this->input->hasOption('basepath') && $this->input->getOption('basepath') > '') {
            $setBy      = 'option `--basepath`';
            $this->base = realpath($this->input->getOption('basepath'));
        }

        $this->build            = $this->base . '/build';
        $this->tests            = $this->base . '/tests';
        $this->bin              = $this->base . '/vendor/bin';
        $this->unitTests        = $this->tests . '/unit';
        $this->integrationTests = $this->tests . '/integration';
        $this->systemTests      = $this->tests . '/system';
        $this->testEnvironments = $this->tests . '/servers';
        $this->serverDockyard   = $this->build . '/servers';
        $this->versionCache     = $this->build . '/versions.json';
        $this->downloadCache    = $this->build . '/cache';
        $this->buildTemplates   = dirname(__DIR__) . '/build';

        $this->output->writeln(
            "Base path set by $setBy to <info>$this->base</info>",
            OutputInterface::VERBOSITY_DEBUG
        );
    }

    protected function addJoomlaPathOption(): Command
    {
        $this->addOption(
            'joomla',
            'j',
            InputOption::VALUE_REQUIRED,
            'The root of the Joomla installation',
            'joomla'
        );

        return $this;
    }

    protected function initJoomlaPath(): void
    {
        $setBy            = 'default';
        $this->joomlaPath = $this->base . '/joomla';

        if (isset($this->project['paths']['joomla'])) {
            $setBy            = 'project.json';
            $this->joomlaPath = rtrim($this->base . '/' . $this->project['paths']['joomla'], '/');
        }

        if ($this->input->hasOption('joomla') && $this->input->getOption('joomla') > '') {
            $setBy            = 'option `--joomla`';
            $option           = $this->input->getOption('joomla');
            $this->joomlaPath = (strpos($option, '/') === 0 ? '' : $this->base . '/') . $option;
        }

        $this->output->writeln(
            "Joomla path set by $setBy to <info>$this->joomlaPath</info>",
            OutputInterface::VERBOSITY_DEBUG
        );
    }

    protected function addSourcePathOption(): Command
    {
        $this->addOption(
            'source',
            's',
            InputOption::VALUE_OPTIONAL,
            'The source directory'
        );

        return $this;
    }

    protected function initSourcePath(): void
    {
        $setBy        = 'default';
        $this->source = $this->base . '/source';

        if (isset($this->project['paths']['source'])) {
            $setBy        = 'project.json';
            $this->source = rtrim($this->base . '/' . $this->project['paths']['source'], '/');
        }

        if ($this->input->hasOption('source') && $this->input->getOption('source') > '') {
            $setBy        = 'option `--source`';
            $this->source = realpath($this->input->getOption('source'));
        }

        $this->output->writeln(
            "Source path set by $setBy to <info>$this->source</info>",
            OutputInterface::VERBOSITY_DEBUG
        );
    }

    protected function addLogPathOption(): Command
    {
        $this->addOption(
            'logs',
            'l',
            InputOption::VALUE_REQUIRED,
            'The logs directory',
            'build/logs'
        );

        return $this;
    }

    protected function initLogPath(): void
    {
        $setBy      = 'default';
        $this->logs = $this->build . '/logs';

        if (isset($this->project['paths']['logs'])) {
            $setBy      = 'project.json';
            $this->logs = rtrim($this->base . '/' . $this->project['paths']['logs'], '/');
        }

        if ($this->input->hasOption('logs') && $this->input->getOption('logs') > '') {
            $setBy      = 'option `--logs`';
            $option     = $this->input->getOption('logs');
            $this->logs = (strpos($option, '/') === 0 ? '' : $this->base . '/') . $option;
        }

        $this->output->writeln(
            "Logs path set by $setBy to <info>$this->logs</info>",
            OutputInterface::VERBOSITY_DEBUG
        );
    }
}
