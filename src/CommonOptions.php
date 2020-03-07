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
            'The root of the project',
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
}
