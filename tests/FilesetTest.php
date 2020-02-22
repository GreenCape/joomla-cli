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

class FilesetTest extends TestCase
{
	private $dir = 'tests/fixtures/fileset';

	/**
	 * @testdox test*.xml will include test_42.xml, but it will not include test/some.xml.
	 */
	public function testFilter1(): void
	{
		$fileset   = new Fileset($this->dir);
		$files     = $fileset
			->include('test*.xml')
			->exclude('**/*.ent.xml')
			->getFiles();
		sort($files);

		$expected  = [
			$this->dir . '/test_42.xml'
		];

		$this->assertEquals($expected, $files);
	}

	/**
	 * @testdox test**.xml fits to test_42.xml as well as to test/some.xml, for example.
	 */
	public function testFilter2(): void
	{
		$fileset  = new Fileset($this->dir);
		$files    = $fileset
			->include('test**.xml')
			->exclude('**/*.ent.xml')
			->getFiles();
		sort($files);

		$expected = [
			$this->dir . '/test/some.xml',
			$this->dir . '/test_42.xml',
		];

		$this->assertEquals($expected, $files);
	}

	/**
	 * @testdox **\/*.ent.xml fits to all files that end with ent.xml in all subdirectories. However, it will not include any files that are directly in the base directory of the file set.
	 */
	public function testFilter3(): void
	{
		$fileset  = new Fileset($this->dir);
		$files    = $fileset->include('**/*.ent.xml')->getFiles();
		sort($files);

		$expected = [
			$this->dir . '/test/test.ent.xml'
		];

		$this->assertEquals($expected, $files);
	}
}
