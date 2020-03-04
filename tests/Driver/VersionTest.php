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

namespace UnitTest\Driver;

use GreenCape\JoomlaCLI\Driver\Version;
use League\Flysystem\Adapter\Local;
use League\Flysystem\FileNotFoundException;
use League\Flysystem\Filesystem;
use PHPUnit\Framework\TestCase;

/**
 * Class VersionTest
 *
 * @testdox Version Driver ...
 */
class VersionTest extends TestCase
{
    use JoomlaPackagesTrait;

    /**
     * @testdox      ... detects Joomla! $release (tested with Joomla! $short)
     *
     * @param  string  $path
     * @param  string  $release
     * @param  string  $short
     * @param  string  $long
     *
     * @throws FileNotFoundException
     * @dataProvider joomlaPackages
     */
    public function testVersion($path, $release, $short, $long): void
    {
        $adapter    = new Local('tests/fixtures/' . $path);
        $filesystem = new Filesystem($adapter);

        $joomlaVersion = new Version($filesystem);

        $this->assertEquals($release, $joomlaVersion->getRelease());
        $this->assertEquals($short, $joomlaVersion->getShortVersion());
        $this->assertEquals($long, $joomlaVersion->getLongVersion());
    }

    /**
     * @testdox ... throws FileNotFoundException if version file is not found
     *
     * @throws FileNotFoundException
     */
    public function testException(): void
    {
        $adapter    = new Local('tests/fixtures/nx');
        $filesystem = new Filesystem($adapter);

        $this->expectException(FileNotFoundException::class);
        new Version($filesystem);
    }
}
