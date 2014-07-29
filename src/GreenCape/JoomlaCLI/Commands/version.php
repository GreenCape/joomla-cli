<?php
/**
 * GreenCape Joomla Command Line Interface
 *
 * Copyright (c) 2012-2013, Niels Braczek <nbraczek@bsds.de>.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *   * Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 *
 *   * Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in
 *     the documentation and/or other materials provided with the
 *     distribution.
 *
 *   * Neither the name of Niels Braczek nor the names of his
 *     contributors may be used to endorse or promote products derived
 *     from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @package     GreenCape\JoomlaCLI
 * @subpackage  Command
 * @author      Niels Braczek <nbraczek@bsds.de>
 * @copyright   2012-2013 Niels Braczek <nbraczek@bsds.de>
 * @license     http://opensource.org/licenses/AGPL-3.0 GNU AFFERO GENERAL PUBLIC LICENSE, Version 3 (AGPL-3.0)
 * @link        http://www.bsds.de/
 * @since       File available since Release 1.0.0
 */

namespace GreenCape\JoomlaCLI;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * The version command reports the version of a Joomla! installation.
 *
 * @package     GreenCape\JoomlaCLI
 * @subpackage  Command
 * @author      Niels Braczek <nbraczek@bsds.de>
 * @copyright   2012-2013 Niels Braczek <nbraczek@bsds.de>
 * @license     http://opensource.org/licenses/AGPL-3.0 GNU AFFERO GENERAL PUBLIC LICENSE, Version 3 (AGPL-3.0)
 * @link        http://www.bsds.de/
 * @since       File available since Release 1.0.0
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
