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
use GreenCape\JoomlaCLI\Command\DownloadCommand;
use GreenCape\JoomlaCLI\Command\OverrideCommand;
use GreenCapeTest\JoomlaPackagesTrait;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\NullOutput;

class OverrideTest extends TestCase
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
	 * @param string $path
	 * @param string $release
	 * @param string $short
	 * @param string $long
	 *
	 * @throws Exception
	 * @dataProvider joomlaPackages
	 */
	public function testOverride($path, $release, $short, $long): void
	{
		$command = new DownloadCommand();
		$output  = new NullOutput();

		$command->run(new StringInput("-b tests/tmp/$path $short"), $output);

		$command = new OverrideCommand();
		$output  = new NullOutput();

		$command->run(new StringInput("-b tests/tmp/$path system"), $output);

		$contents = array_reduce(
			self::$filesystem->listContents("tmp/$path/templates/system/html"),
			static function ($carry, $item) {
				$carry[] = $item['basename'];
				return $carry;
			},
			[]
		);
		$this->assertTrue($release === '1.0' || count($contents) > 2);
	}

	protected function tearDown(): void
	{
		self::$filesystem->deleteDir('tmp');
	}
}
