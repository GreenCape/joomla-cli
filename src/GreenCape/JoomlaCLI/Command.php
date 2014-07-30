<?php
/**
 * GreenCape Joomla Command Line Interface
 *
 * Copyright (c) 2012-2014, Niels Braczek <nbraczek@bsds.de>.
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
 *   * Neither the name of GreenCape or Niels Braczek nor the names of his
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
 * @copyright   (C) 2012-2014 GreenCape, Niels Braczek <nbraczek@bsds.de>
 * @license     http://opensource.org/licenses/GPL-2.0 GNU General Public License, version 2.0 (GPLv2)
 * @link        http://www.greencape.com/
 * @since       File available since Release 0.1.0
 */

namespace GreenCape\JoomlaCLI;

use Symfony\Component\Console\Command\Command as BaseCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * The abstract command provides common methods for most JoomlaCLI commands.
 *
 * @package     GreenCape\JoomlaCLI
 * @subpackage  Command
 * @author      Niels Braczek <nbraczek@bsds.de>
 * @copyright   (C) 2012-2014 GreenCape, Niels Braczek <nbraczek@bsds.de>
 * @license     http://opensource.org/licenses/GPL-2.0 GNU General Public License, version 2.0 (GPLv2)
 * @link        http://www.greencape.com/
 * @since       File available since Release 1.0.0
 */
abstract class Command extends BaseCommand
{
	/** @var JoomlaDriver */
	protected $joomla;

	/**
	 * Constructor.
	 *
	 * @param   string  $name  The name of the command
	 */
	public function __construct($name = null)
	{
		parent::__construct($name);
		$this->addGlobalOptions();
	}

	/**
	 * Add options common to all commands
	 *
	 * @return  void
	 */
	protected function addGlobalOptions()
	{
		$this
			->addOption(
				'basepath',
				'b',
				InputOption::VALUE_REQUIRED,
				'The root of the Joomla! installation. Defaults to the current working directory.',
				'.'
			)
		;
	}

	/**
	 * Setup the environment
	 *
	 * @param   string           $application  The application, eg., 'site' or 'administration'
	 * @param   InputInterface   $input        An InputInterface instance
	 * @param   OutputInterface  $output       An OutputInterface instance
	 *
	 * @return  void
	 */
	protected function setupEnvironment($application, InputInterface $input, OutputInterface $output)
	{
		$basePath = $this->handleBasePath($input, $output);
		$driverFactory = new DriverFactory;
		$this->joomla = $driverFactory->create($basePath);

		$this->joomla->setupEnvironment($basePath, $application);
	}

	/**
	 * Read the base path from the options
	 *
	 * @param   InputInterface   $input   An InputInterface instance
	 * @param   OutputInterface  $output  An OutputInterface instance
	 *
	 * @return  string  The base path
	 */
	protected function handleBasePath(InputInterface $input, OutputInterface $output)
	{
		$path = realpath($input->getOption('basepath'));
		$this->writeln($output, 'Joomla! installation expected in ' . $path, OutputInterface::VERBOSITY_DEBUG);

		return $path;
	}

	/**
	 * Proxy for OutputInterface::writeln()
	 *
	 * @param   OutputInterface  $output  An OutputInterface instance
	 * @param   string|array     $message
	 * @param   int              $level    One of OutputInterface::VERBOSITY_*
	 * @param   int              $mode     One of OutputInterface::OUTPUT_*
	 *
	 * @return  void
	 */
	protected function writeln(OutputInterface $output, $message, $level = OutputInterface::VERBOSITY_NORMAL, $mode = OutputInterface::OUTPUT_NORMAL)
	{
		if ($output->getVerbosity() >= $level)
		{
			$output->writeln($message, $mode);
		}
	}
}
