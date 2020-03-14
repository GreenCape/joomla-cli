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

namespace UnitTest;

use GreenCape\JoomlaCLI\Application;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\BufferedOutput;

/**
 * Class ApplicationTest
 *
 * @testdox Application ...
 */
class ApplicationTest extends TestCase
{
    /** @var  Application */
    private $console;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    public function setUp(): void
    {
        $this->console = new Application();
        $this->console->setAutoExit(false);
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    public function tearDown(): void
    {
    }

    /**
     * @return array
     */
    public function commandNameProvider(): array
    {
        return [
            'version'  => ['core:version'],
            'download' => ['core:download'],
            'install'  => ['extension:install'],
            'override' => ['template:override'],
        ];
    }

    /**
     * @testdox      ... knows command `$command`
     *
     * @dataProvider commandNameProvider
     *
     * @param  string  $command
     */
    public function testCommandIsDefined($command): void
    {
        $this->assertTrue($this->console->has($command));
    }

    /**
     * @testdox ... forwards arguments to command
     *
     * @throws \Exception
     */
    public function testCall(): void
    {
        $input  = new StringInput('core:version --joomla=tests/fixtures/j39 --release');
        $output = new BufferedOutput();

        $this->console->run($input, $output);

        $this->assertEquals('3.9', trim($output->fetch()));
    }

    /**
     * @testdox ... issues error message on failures
     *
     * @throws \Exception
     */
    public function testException(): void
    {
        $input  = new StringInput('core:version --joomla=tests/fixtures/nx --release');
        $output = new BufferedOutput();

        $this->console->run($input, $output);

        $this->assertRegExp('~Joomla CLI version.*?\n\nFile not found~', $output->fetch());
    }
}
