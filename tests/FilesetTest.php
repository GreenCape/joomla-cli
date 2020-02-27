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
 * @since           File available since Release 1.1.0
 */

namespace GreenCapeTest;

use GreenCape\JoomlaCLI\Fileset;
use PHPUnit\Framework\TestCase;

/**
 * Class FilesetTest
 *
 * @testdox FileSet ...
 */
class FilesetTest extends TestCase
{
    private $dir = 'tests/fixtures/fileset';

    /**
     * @testdox ... includes `test_42.xml` for pattern 'test*.xml', but it does not include `test/some.xml`.
     */
    public function testFilter1(): void
    {
        $fileset = new Fileset($this->dir);
        $files   = $fileset
            ->include('test*.xml')
            ->exclude('**/*.ent.xml')
            ->getFiles()
        ;
        sort($files);

        $expected = [
            $this->dir . '/test_42.xml',
        ];

        $this->assertEquals($expected, $files);
    }

    /**
     * @testdox ... includes `test_42.xml` as well as `test/some.xml` for pattern 'test**.xml'.
     */
    public function testFilter2(): void
    {
        $fileset = new Fileset($this->dir);
        $files   = $fileset
            ->include('test**.xml')
            ->exclude('**/*.ent.xml')
            ->getFiles()
        ;
        sort($files);

        $expected = [
            $this->dir . '/test/some.xml',
            $this->dir . '/test_42.xml',
        ];

        $this->assertEquals($expected, $files);
    }

    /**
     * @testdox ... includes all files that end with 'ent.xml' in all subdirectories for pattern '**\/*.ent.xml'.
     */
    public function testFilter3(): void
    {
        $fileset = new Fileset($this->dir);
        $files   = $fileset->include('**/*.ent.xml')->getFiles();
        sort($files);

        $expected = [
            $this->dir . '/test/test.ent.xml',
        ];

        $this->assertEquals($expected, $files);
    }

    /**
     * @testdox ... does not include files directly in the base directory of the file set for pattern '**\/*.ent.xml'.
     */
    public function testFilter4(): void
    {
        $fileset = new Fileset($this->dir);
        $files   = $fileset->include('**/*.ent.xml')->getFiles();
        sort($files);

        $expected = [
            $this->dir . '/test/test.ent.xml',
        ];

        $this->assertEquals($expected, $files);
    }
}
