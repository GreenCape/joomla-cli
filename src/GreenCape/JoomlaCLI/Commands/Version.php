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
 * @subpackage  Command
 * @author      Niels Braczek <nbraczek@bsds.de>
 * @copyright   (C) 2012-2015 GreenCape, Niels Braczek <nbraczek@bsds.de>
 * @license     http://opensource.org/licenses/MIT The MIT license (MIT)
 * @link        http://greencape.github.io
 * @since       File available since Release 0.1.0
 */

namespace GreenCape\JoomlaCLI;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * The version command reports the version of a Joomla! installation.
 *
 * @package     GreenCape\JoomlaCLI
 * @subpackage  Command
 * @since       Class available since Release 1.0.0
 */
class VersionCommand extends Command
{
	/**
	 * Configure the options for the version command
	 *
	 * @return  void
	 */
	protected function configure()
	{
		$this
			->setName('version')

			->setDescription('Show the Joomla! version')

			->addOption(
				'long',
				'l',
				InputOption::VALUE_NONE,
				'The long version info, eg. Joomla! x.y.z Stable [ Codename ] DD-Month-YYYY HH:ii GMT (default).'
			)

			->addOption(
				'short',
				's',
				InputOption::VALUE_NONE,
				'The short version info, eg. x.y.z'
			)

			->addOption(
				'release',
				'r',
				InputOption::VALUE_NONE,
				'The release info, eg. x.y'
			)
		;
	}

	/**
	 * Execute the version command
	 *
	 * @param   InputInterface   $input   An InputInterface instance
	 * @param   OutputInterface  $output  An OutputInterface instance
	 *
	 * @return  void
	 */
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$this->setupEnvironment('site', $input, $output);

		$version = new \JVersion;

		if ($input->getOption('short'))
		{
			$result = $version->getShortVersion();
		}
		elseif ($input->getOption('release'))
		{
			$result = $version->RELEASE;
		}
		else
		{
			$result = $version->getLongVersion();
		}
		$output->writeln($result);
	}
}
