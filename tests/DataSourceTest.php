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
 * @since           File available since Release 0.2.0
 */

namespace GreenCapeTest;

use Exception;
use GreenCape\JoomlaCLI\Command\Core\DownloadCommand;
use GreenCape\JoomlaCLI\Command\Core\InstallCommand;
use GreenCape\JoomlaCLI\DataSource;
use GreenCapeTest\JoomlaPackagesTrait;
use mysqli as MySQLi;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\NullOutput;

class DataSourceTest extends TestCase
{
	/**
	 * @return array
	 */
	public function dsnSamples(): array
	{
		return [
			['user:pass@host:3306/base'],
			['user:pass@host:3306'],
			['user:pass@host/base'],
			['user:pass@host'],
			['host:3306/base'],
			['user@host:3306/base'],
		];
	}

	/**
	 * @dataProvider dsnSamples
	 * @testdox DSN strings are parsed correctly
	 */
	public function testPattern($sample): void
	{
		$dsn = new DataSource($sample, 'user:pass@host:3306/base');

		$this->assertEquals('user', $dsn->getUser());
		$this->assertEquals('pass', $dsn->getPass());
		$this->assertEquals('host', $dsn->getHost());
		$this->assertEquals('3306', $dsn->getPort());
		$this->assertEquals('base', $dsn->getBase());
	}

	/**
	 * @testdox IP addresses are recognised as host as well
	 */
	public function testIP(): void
	{
		$dsn = new DataSource('http://127.0.0.1:3307', 'user:pass@host:3306/base');

		$this->assertEquals('http://127.0.0.1', $dsn->getHost());
		$this->assertEquals('3307', $dsn->getPort());
	}
}
