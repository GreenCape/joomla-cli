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

namespace GreenCapeTest\Command\Core;

use Exception;
use GreenCape\JoomlaCLI\Command\Core\DownloadCommand;
use GreenCapeTest\Driver\JoomlaPackagesTrait;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\NullOutput;

class DownloadTest extends TestCase
{
    /**
     * @var Filesystem
     */
    private static $filesystem;

    use JoomlaPackagesTrait;

    /**
     */
    public static function setUpBeforeClass(): void
    {
        self::$filesystem = new Filesystem(new Local('tests'));
    }

    /**
     * @param  string  $path
     * @param  string  $release
     * @param  string  $short
     * @param  string  $long
     *
     * @throws Exception
     * @dataProvider joomlaPackages
     * @testdox      Command `download` finds the correct source for different versions
     */
    public function testDownload($path, $release, $short, $long): void
    {
        $command = new DownloadCommand();
        $output  = new BufferedOutput();

        $command->run(new StringInput("-b tests/tmp/$path -c tests/tmp/cache $short"), $output);

        $this->assertFileExists("tests/tmp/$path/index.php",
            "Expected files in tests/tmp/$path, but mandatory index.php was not found");

        $time1 = filemtime("tests/tmp/cache/{$short}.tar.gz");
        $command->run(new StringInput("-b tests/tmp/$path -c tests/tmp/cache $short"), $output);
        $time2 = filemtime("tests/tmp/cache/{$short}.tar.gz");

        $this->assertEquals($time1, $time2, 'Second call should have been served from the cache');
    }

    /**
     * @throws Exception
     * @testdox Command `download` finds the correct source for branches
     */
    public function testDownload2(): void
    {
        $command = new DownloadCommand();
        $output  = new NullOutput();

        $command->run(new StringInput('-b tests/tmp/staging staging'), $output);
        $this->assertFileExists('tests/tmp/staging/index.php',
            'Expected files in tests/tmp/staging, but mandatory index.php was not found');
    }

    /**
     * @throws Exception
     * @testdox Trying to download a non-existent version causes a message
     */
    public function testDownload3(): void
    {
        $command = new DownloadCommand();
        $output  = new BufferedOutput();

        $this->expectExceptionMessage('nx: Version is unknown');

        $command->run(new StringInput('-b tests/tmp/nx nx'), $output);
    }

    protected function tearDown(): void
    {
        self::$filesystem->deleteDir('tmp');
    }
}
