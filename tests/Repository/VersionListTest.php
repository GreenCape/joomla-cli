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

namespace GreenCapeTest\Repository;

use GreenCape\JoomlaCLI\Repository\VersionList;
use League\Flysystem\FileNotFoundException;
use League\Flysystem\Filesystem;
use League\Flysystem\Memory\MemoryAdapter;
use PHPUnit\Framework\TestCase;

/**
 * Class VersionListTest
 *
 * @testdox Joomla Repositories
 */
class VersionListTest extends TestCase
{
    /**
     * @var Filesystem
     */
    private static $filesystem;
    /**
     * @var string
     */
    private static $cacheFile;
    /**
     * @var VersionList
     */
    private static $versionList;

    /**
     */
    public static function setUpBeforeClass(): void
    {
        self::$filesystem = new Filesystem(new MemoryAdapter());
        self::$cacheFile  = 'versions.json';
    }

    /**
     * @throws FileNotFoundException
     */
    public function setUp(): void
    {
        self::$versionList = new VersionList(self::$filesystem, self::$cacheFile);
    }

    /**
     * @testdox Version list contains branches, tags and aliases
     *
     * @throws FileNotFoundException
     */
    public function testALot(): void
    {
        $list = json_decode(self::$filesystem->read(self::$cacheFile), true);
        $this->assertArrayHasKey('heads', $list);
        $this->assertArrayHasKey('tags', $list);
        $this->assertArrayHasKey('alias', $list);
    }

    /**
     * @testdox Incomplete versions are resolved correctly
     */
    public function testALot2(): void
    {
        $this->assertEquals('1.5.26', self::$versionList->resolve('1'));
        $this->assertEquals('1.5.26', self::$versionList->resolve('1.5'));
    }

    /**
     * @testdox Branches and tags are recognised and distinguished
     */
    public function testALot3(): void
    {
        $this->assertFalse(self::$versionList->isBranch('3.5.0'), 'Tag `3.5.0` is not a branch name');
        $this->assertTrue(self::$versionList->isBranch('staging'), '`Branch `staging` should be recognised');

        $this->assertTrue(self::$versionList->isTag('3.5.0'), 'Tag `3.5.0` should be recognised');
        $this->assertFalse(self::$versionList->isTag('staging'), '`Branch `staging` is not a tag');
    }

    /**
     * @testdox The right repository for a version is provided
     */
    public function testALot4(): void
    {
        $this->assertEquals('greencape/joomla-legacy', self::$versionList->getRepository('1.0.0'));
        $this->assertEquals('joomla/joomla-cms', self::$versionList->getRepository('3.5.0'));
    }

    /**
     * @testdox The version list is cached
     *
     * @throws FileNotFoundException
     */
    public function testReuse(): void
    {
        $time1 = self::$filesystem->getTimestamp(self::$cacheFile);
        /** @noinspection PhpUnusedLocalVariableInspection */
        $versionList = new VersionList(self::$filesystem, self::$cacheFile);
        $time2       = self::$filesystem->getTimestamp(self::$cacheFile);

        $this->assertEquals($time1, $time2);
    }
}
