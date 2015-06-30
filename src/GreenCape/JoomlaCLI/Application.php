<?php
/**
 * GreenCape Joomla Command Line Interface
 *
 * MIT License
 *
 * Copyright (c) 2012-2015, Niels Braczek <nbraczek@bsds.de>. All rights reserved.
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
 * @package     GreenCape\JoomlaCLI
 * @subpackage  Core
 * @author      Niels Braczek <nbraczek@bsds.de>
 * @copyright   (C) 2012-2015 GreenCape, Niels Braczek <nbraczek@bsds.de>
 * @license     http://opensource.org/licenses/MIT The MIT license (MIT)
 * @link        http://greencape.github.io
 * @since       File available since Release 0.1.0
 */

namespace GreenCape\JoomlaCLI;

use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * The main Joomla CLI application.
 *
 * @package     GreenCape\JoomlaCLI
 * @subpackage  Core
 * @since       Class available since Release 0.1.0
 */
class Application extends BaseApplication
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct('Joomla CLI', '0.1.0');
		$this->setCatchExceptions(false);
		$this->addPlugins(__DIR__ . '/Commands');
	}

	/**
	 * Runs the current application.
	 *
	 * @param   InputInterface   $input   An InputInterface instance
	 * @param   OutputInterface  $output  An OutputInterface instance
	 *
	 * @return  integer  0 if everything went fine, or an error code
	 *
	 * @throws  \Exception on problems
	 */
	public function run(InputInterface $input = null, OutputInterface $output = null)
	{
		try
		{
			parent::run($input, $output);
		}
		catch (\Exception $e)
		{
			if (null === $output) {
				$output = new ConsoleOutput();
			}
			$message = array(
				$this->getLongVersion(),
				'',
				$e->getMessage(),
				''
			);
			$output->writeln($message);
		}
	}

	/**
	 * Dynamically add all commands from a path
	 *
	 * @param   string  $path  The directory with the plugins
	 *
	 * @return  void
	 */
	private function addPlugins($path)
	{
		foreach (glob($path . '/*.php') as $filename)
		{
			$commandClass = __NAMESPACE__ . '\\' . basename($filename, '.php') . 'Command';
			$command = new $commandClass;
			$this->add($command);
		}
	}
}
