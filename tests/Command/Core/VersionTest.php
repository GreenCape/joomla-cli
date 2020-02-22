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
 * @package         GreenCape\JoomlaCLI
 * @subpackage      Unittests
 * @author          Niels Braczek <nbraczek@bsds.de>
 * @copyright   (C) 2012-2019 GreenCape, Niels Braczek <nbraczek@bsds.de>
 * @license         http://opensource.org/licenses/MIT The MIT license (MIT)
 * @link            http://greencape.github.io
 * @since           File available since Release 0.1.0
 */

namespace GreenCapeTest\Command;

use Exception;
use GreenCape\JoomlaCLI\Command\Core\VersionCommand;
use GreenCapeTest\Driver\JoomlaPackagesTrait;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\BufferedOutput;

class VersionTest extends TestCase
{
    use JoomlaPackagesTrait;

    /**
     * @param  string  $path
     * @param  string  $release
     * @param  string  $short
     * @param  string  $long
     *
     * @throws Exception
     * @dataProvider joomlaPackages
     * @testdox      Command `version` detects the correct version
     */
    public function testVersion($path, $release, $short, $long): void
    {
        $command = new VersionCommand();
        $output  = new BufferedOutput();

        $command->run(new StringInput('-b tests/fixtures/' . $path), $output);
        $this->assertEquals($long, trim($output->fetch()), '`version` with no option should return ' . $long);

        $command->run(new StringInput('--long -b tests/fixtures/' . $path), $output);
        $this->assertEquals($long, trim($output->fetch()), '`version` with option `--long` should return ' . $long);

        $command->run(new StringInput('-l -b tests/fixtures/' . $path), $output);
        $this->assertEquals($long, trim($output->fetch()), '`version` with option `-l` should return ' . $long);

        $command->run(new StringInput('--release -b tests/fixtures/' . $path), $output);
        $this->assertEquals($release, trim($output->fetch()),
            '`version` with option `--release` should return ' . $release);

        $command->run(new StringInput('-r -b tests/fixtures/' . $path), $output);
        $this->assertEquals($release, trim($output->fetch()), '`version` with option `-r` should return ' . $release);

        $command->run(new StringInput('--short -b tests/fixtures/' . $path), $output);
        $this->assertEquals($short, trim($output->fetch()), '`version` with option `--short` should return ' . $short);

        $command->run(new StringInput('-s -b tests/fixtures/' . $path), $output);
        $this->assertEquals($short, trim($output->fetch()), '`version` with option `-s` should return ' . $short);
    }
}
