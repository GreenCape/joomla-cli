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

namespace GreenCapeTest\Command\Core;

use Exception;
use GreenCape\JoomlaCLI\Command\Core\DownloadCommand;
use GreenCape\JoomlaCLI\Command\Core\InstallCommand;
use GreenCape\JoomlaCLI\DataSource;
use GreenCapeTest\Driver\JoomlaPackagesTrait;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\NullOutput;

class InstallationTest extends TestCase
{
	private static $container = 'fixtures_db_1';

	private static $composeFile = '--file tests/fixtures/maria-db.yml';

	private $admin = 'admin:admin';

	private $database = 'sqladmin:sqladmin@http://127.0.0.1:3306/database';

	use JoomlaPackagesTrait;

	public static function setUpBeforeClass(): void
	{
		shell_exec('docker-compose ' . self::$composeFile . ' up -d 2>&1');

		for ($t = 0; $t < 10; ++$t)
		{
			sleep(1);
			if (self::isReady(self::$container))
			{
				return;
			}
		}
		self::markTestSkipped('Could not start database container within 10 seconds');
	}

	public static function tearDownAfterClass(): void
	{
		#shell_exec('docker-compose ' . self::$composeFile . ' down 2>&1');
	}

	/**
	 * @param $container
	 *
	 * @return bool
	 */
	private static function isReady($container): bool
	{
		return strpos(shell_exec("docker logs {$container} 2>&1"), 'mysqld: ready for connections.') !== false;
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
	public function testInstall($path, $release, $short, $long): void
	{
		$this->setupFilesystem($path, $short);

		$command = new InstallCommand();
		$output  = new NullOutput();
		[$admin, $password] = explode(':', $this->admin);
		$prefix = 'j' . str_replace('.', '', $release);

		$options = implode(
			' ',
			[
				'--admin=' . $this->admin,
				'--db-type=mysqli',
				'--database=' . $this->database,
				'--root=root',
				'--prefix=' . $prefix
			]
		);
		$command->run(new StringInput("-b tests/tmp/$path $options"), $output);

		$dsn = new DataSource($this->database);

		$container = self::$container;
		$user      = $dsn->getUser();
		$pass      = $dsn->getPass();
		$base      = $dsn->getBase();
		file_put_contents("tests/tmp/{$path}/query.sql", "SELECT * FROM {$prefix}_users WHERE username='{$admin}'");
		$result = shell_exec("docker exec -i {$container} sh -c 'exec mysql -u{$user} -p\"{$pass}\" {$base}' < tests/tmp/{$path}/query.sql 2>&1");

		$this->assertEquals('Foo', $result, "MySQL: {$result}");
	}

	/**
	 * @param $path
	 * @param $short
	 *
	 * @throws Exception
	 */
	private function setupFilesystem($path, $short): void
	{
		(new DownloadCommand())->run(new StringInput("-b tests/tmp/$path -c tests/tmp/cache $short"), new NullOutput());
	}
}
