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

namespace UnitTest;

use GreenCape\JoomlaCLI\Fileset;
use GreenCape\JoomlaCLI\FilesystemMethods;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\NullOutput;

/**
 * Class FilesetTest
 *
 * @testdox Filesystem Methods
 */
class FilesystemMethodsTest extends TestCase
{
    private $testDir = 'tmp/fs-methods';

    private $fixtures = 'tests/fixtures';

    use FilesystemMethods;

    public function setUp(): void
    {
        $this->output = new NullOutput();

        if (file_exists($this->testDir)) {
            shell_exec("rm -rf {$this->testDir}");
        }

        mkdir($this->testDir);
    }

    public function tearDown(): void
    {
        if (file_exists($this->testDir)) {
            shell_exec("rm -rf {$this->testDir}");
        }
    }

    /**
     * @testdox `reflexive()` applies filter to Fileset
     */
    public function testReflexiveFileset(): void
    {
        $testFile = $this->testDir . '/reflexive.txt';
        $content  = 'Variable is replaced by ${variable}.';

        file_put_contents($testFile, $content);

        $fileset = new Fileset($this->testDir);
        $fileset->include('*.txt');

        $this->reflexive(
            $fileset,
            static function ($content) {
                return str_replace('${variable}', 'value', $content);
            }
        );

        $expected = 'Variable is replaced by value.';
        $actual   = file_get_contents($testFile);

        $this->assertEquals($expected, $actual);
    }

    /**
     * @testdox `reflexive()` applies filter to file
     */
    public function testReflexiveFile(): void
    {
        $testFile = $this->testDir . '/reflexive.txt';
        $content  = 'Variable is replaced by ${variable}.';

        file_put_contents($testFile, $content);

        $this->reflexive(
            $testFile,
            static function ($content) {
                return str_replace('${variable}', 'value', $content);
            }
        );

        $expected = 'Variable is replaced by value.';
        $actual   = file_get_contents($testFile);

        $this->assertEquals($expected, $actual);
    }

    /**
     * @testdox `isUptodate()` returns true, if target is newer than source
     */
    public function testUptodate(): void
    {
        $target = $this->testDir . '/target.txt';
        $source = $this->testDir . '/source.txt';

        touch($source, time() - 100);
        touch($target, time());

        $this->assertGreaterThan(filemtime($source), filemtime($target));
        $this->assertTrue($this->isUptodate($target, $source));
    }

    /**
     * @testdox `isUptodate()` returns false, if target is not all newer than source
     */
    public function testUptodate2(): void
    {
        $target = $this->testDir . '/target.txt';
        $source = $this->testDir . '/source.txt';

        touch($source, time());
        touch($target, time() - 100);

        $this->assertGreaterThan(filemtime($target), filemtime($source));
        $this->assertFalse($this->isUptodate($target, $source));
    }

    /**
     * @return array
     */
    public function versionData(): array
    {
        return [
            ['1.0.0', 'version-1.txt'],
            ['1.1.0', 'version-1.txt'],
            ['1.1.1', 'version-1.txt'],
            ['1.2.0', 'version-1.txt'],
            ['1.5.0', 'version-1.5.txt'],
            ['1.7.0', 'version-1.5.txt'],
            ['2.5.0', 'version-2.5.txt'],
            ['3.5.0', 'version-3.txt'],
            ['3.8.0', 'version-3.8.txt'],
            ['3.8.4', 'version-3.8.txt'],
            ['3.8.5', 'version-3.8.5.txt'],
            ['3.9.0', 'version-3.8.5.txt'],
        ];
    }

    /**
     * @testdox      `versionMatch()` retrieves the best matching file (i.e., $match for $version)
     * @dataProvider versionData
     *
     * @param $version
     * @param $match
     */
    public function testVersionMatch($version, $match): void
    {
        $expected = $match;
        $actual   = $this->versionMatch(
            'version-([\d.]+)\.txt',
            $this->fixtures . '/version-match',
            $version
        );

        $this->assertEquals($expected, basename($actual));
    }

    /**
     * @testdox `mkdir()` creates directories
     */
    public function testMkDirPlain(): void
    {
        $testDir = $this->testDir . '/subdir';

        if (file_exists($testDir)) {
            rmdir($testDir);
        }

        $this->mkdir($testDir);

        $this->assertDirectoryExists($testDir);
    }

    /**
     * @testdox `mkdir()` does not fail on existing directories
     * @depends testMkDirPlain
     */
    public function testMkDirExisting(): void
    {
        $testDir = $this->testDir . '/subdir';

        $this->mkdir($testDir);

        $this->assertDirectoryExists($testDir);
    }

    /**
     * @testdox `mkdir()` optionally creates an empty `index.html` file
     * @depends testMkDirPlain
     */
    public function testMkDirIndex(): void
    {
        $testDir = $this->testDir . '/subdir';

        $this->mkdir($testDir, true);

        $this->assertFileExists($testDir . '/index.html');
    }

    /**
     * @testdox `mkdir()` throws an exception when trying to create a directory that is a file already
     */
    public function testMkDirException(): void
    {
        $testFile = $this->testDir . '/not-a-dir';
        touch($testFile);

        $this->expectException(\RuntimeException::class);
        $this->mkdir($testFile);
    }

    /**
     * @testdox `copy()` copies a Fileset, creating target directory if needed
     */
    public function testCopyFileset(): void
    {
        $source = $this->fixtures . '/copy';
        $target = $this->testDir . '/delete';

        $fileset = new Fileset($source);
        $fileset->include('**.txt');

        $this->copy($fileset, $target);

        $this->assertDirectoryExists($target);
        $this->assertFileExists($target . '/file1.txt');
        $this->assertFileExists($target . '/file2.txt');
        $this->assertFileExists($target . '/dir/file3.txt');
        $this->assertFileNotExists($target . '/no-text.foo');
    }

    /**
     * @testdox `copy()` copies a single file
     */
    public function testCopyFile(): void
    {
        $this->copy($this->fixtures . '/copy.txt', $this->testDir . '/delete/file4.txt');

        $this->assertFileExists($this->testDir . '/delete/file4.txt');
    }

    /**
     * @testdox `delete()` deletes a single file
     */
    public function testDeleteFile(): void
    {
        $this->copy($this->fixtures . '/copy.txt', $this->testDir . '/delete/file4.txt');

        $this->assertFileExists($this->testDir . '/delete/file4.txt');

        $this->delete($this->testDir . '/delete/file4.txt');

        $this->assertFileNotExists($this->testDir . '/delete/file4.txt');
    }

    /**
     * @testdox `delete()` deletes a Fileset
     */
    public function testDeleteFileset(): void
    {
        $target = $this->testDir . '/delete';

        $this->testCopyFileset();

        $fileset = new Fileset($target);
        $fileset->include('**.txt');

        $this->delete($fileset);

        $this->assertDirectoryExists($target);
        $this->assertDirectoryExists($target . '/dir');
        $this->assertFileNotExists($target . '/file1.txt');
        $this->assertFileNotExists($target . '/file2.txt');
        $this->assertFileNotExists($target . '/dir/file3.txt');
    }

    /**
     * @testdox `delete()` deletes non-empty directories
     */
    public function testDeleteDirectory(): void
    {
        $target = $this->testDir . '/delete';

        $this->testCopyFileset();

        $this->assertFileExists($target);

        $this->delete($target);

        $this->assertFileNotExists($target);
    }

    /**
     * @testdox `delete()` ignores non-existent files
     */
    public function testDeleteNonExistent(): void
    {
        $target = $this->testDir . '/delete/foo';

        $this->assertFileNotExists($target);

        $this->delete($target);

        $this->assertFileNotExists($target);
    }
}
