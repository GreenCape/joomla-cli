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

use Symfony\Component\Console\Output\OutputInterface;

class Shell
{
    /**
     * @var OutputInterface
     */
    private $output;

    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
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
    public function exec(string $command, string $dir = '.', bool $passthru = true): int
    {
        $this->output->writeln("Running `$command` in `$dir`", OutputInterface::VERBOSITY_DEBUG);

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

}
