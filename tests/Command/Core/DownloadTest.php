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

namespace UnitTest\Command;

use Exception;
use GreenCape\JoomlaCLI\Command\Core\DownloadCommand;
use GreenCape\JoomlaCLI\FilesystemMethods;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;
use UnitTest\Driver\JoomlaPackagesTrait;

/**
 * Class DownloadTest
 *
 * @testdox Command `core:download` ...
 */
class DownloadTest extends TestCase
{
    /**
     * @var string
     */
    private $tmpDir = 'tmp';

    /**
     * @var OutputInterface
     */
    protected $output;

    use JoomlaPackagesTrait, FilesystemMethods;

    public function setUp(): void
    {
        $this->output = new NullOutput();
    }

    /**
     * @testdox      ... finds the correct source for Joomla! $release (tested with (Joomla! $short)
     *
     * @param  string  $path
     * @param  string  $release
     * @param  string  $short
     * @param  string  $long
     *
     * @throws Exception
     * @dataProvider joomlaPackages
     */
    public function testDownload($path, $release, $short, $long): void
    {
        $command = new DownloadCommand();

        $dir = $this->tmpDir . '/' . $path;
        $cacheFile = "{$this->tmpDir}/cache/{$short}.tar.gz";
        $arguments = "--joomla={$dir} --cache={$this->tmpDir}/cache {$short}";

        $this->delete($dir);
        $this->delete($cacheFile);

        $command->run(new StringInput($arguments), $this->output);

        $this->assertFileExists(
            "{$dir}/index.php",
            "Expected files in {$dir}, but mandatory index.php was not found"
        );

        $time1 = filemtime($cacheFile);
        $command->run(new StringInput($arguments), $this->output);
        $time2 = filemtime($cacheFile);

        $this->assertEquals($time1, $time2, 'Second call should have been served from the cache');
    }

    /**
     * @testdox ... finds the correct source for branches (tested with `staging`)
     *
     * @throws Exception
     */
    public function testDownload2(): void
    {
        $command = new DownloadCommand();

        $dir = $this->tmpDir . '/staging';

        $this->delete($dir);
        $command->run(new StringInput("--joomla={$dir} staging"), $this->output);
        $this->assertFileExists(
            "{$dir}/index.php",
            "Expected files in {$dir}, but mandatory index.php was not found"
        );
    }

    /**
     * @testdox ... throws an exception when trying to download a non-existent version (tested with `nx`)
     *
     * @throws Exception
     */
    public function testDownload3(): void
    {
        $command = new DownloadCommand();

        $this->expectExceptionMessage('nx: Version is unknown');

        $command->run(new StringInput("-joomla={$this->tmpDir}/nx nx"), $this->output);
    }
}
