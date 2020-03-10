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

/**
 * Common Options Trait
 *
 * Provides commonly used options for consistency
 *
 * @since  Class available since Release __DEPLOY_VERSION__
 */
trait CommonOptions
{
    /**
     * @var string
     */
    protected $basePath;

    /**
     * @var Environment
     */
    protected $environment;

    /**
     * @var string
     */
    protected $joomlaPath;

    /**
     * @var string
     */
    protected $sourcePath;

    /**
     * @var string
     */
    protected $logPath;

    /**
     * @param  InputInterface  $input
     */
    protected function initialiseGlobalOptions(InputInterface $input): void
    {
        if ($input->hasOption('basepath')) {
            $this->basePath = $input->getOption('basepath');
        }

        if ($input->hasOption('environment')) {
            $this->environment = new Environment($input->getOption('environment'));
        }

        if ($input->hasOption('joomla')) {
            $this->joomlaPath = $input->getOption('joomla');
        }

        if ($input->hasOption('source')) {
            $this->sourcePath = $input->getOption('source');
        }

        if ($input->hasOption('logs')) {
            $this->logPath = $input->getOption('logs');
        }
    }

    protected function addEnvironmentOption(): Command
    {
        $this->addOption(
            'environment',
            'e',
            InputOption::VALUE_REQUIRED,
            'The environment definition',
            ''
        );

        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this;
    }

    protected function addBasePathOption(): Command
    {
        $this->addOption(
            'basepath',
            'b',
            InputOption::VALUE_REQUIRED,
            'A path to strip from the front of file paths inside reports',
            '.'
        );

        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this;
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

        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this;
    }

    protected function addSourcePathOption(): Command
    {
        $this->addOption(
            'source',
            's',
            InputOption::VALUE_REQUIRED,
            'The source directory',
            'source'
        );

        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this;
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

        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this;
    }
}
