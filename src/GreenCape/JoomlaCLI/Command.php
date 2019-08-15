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
 * @subpackage      Command
 * @author          Niels Braczek <nbraczek@bsds.de>
 * @copyright   (C) 2012-2019 GreenCape, Niels Braczek <nbraczek@bsds.de>
 * @license         http://opensource.org/licenses/MIT The MIT license (MIT)
 * @link            http://greencape.github.io
 * @since           File available since Release 0.1.0
 */

namespace GreenCape\JoomlaCLI;

use Symfony\Component\Console\Command\Command as BaseCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * The abstract command provides common methods for most JoomlaCLI commands.
 *
 * @package     GreenCape\JoomlaCLI
 * @subpackage  Command
 * @since       Class available since Release 0.1.0
 */
abstract class Command extends BaseCommand
{
	/** @var JoomlaDriver */
	protected $joomla;

	/**
	 * Constructor.
	 *
	 * @param string $name The name of the command
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
	protected function addGlobalOptions(): void
	{
		$this
			->addOption(
				'basepath',
				'b',
				InputOption::VALUE_REQUIRED,
				'The root of the Joomla! installation. Defaults to the current working directory.',
				'.'
			);
	}

	/**
	 * Setup the environment
	 *
	 * @param string          $application The application, eg., 'site' or 'administration'
	 * @param InputInterface  $input       An InputInterface instance
	 * @param OutputInterface $output      An OutputInterface instance
	 *
	 * @return  void
	 */
	protected function setupEnvironment($application, InputInterface $input, OutputInterface $output): void
	{
		$basePath      = $this->handleBasePath($input, $output);
		$driverFactory = new DriverFactory;
		$this->joomla  = $driverFactory->create($basePath);

		$this->joomla->setupEnvironment($basePath, $application);
	}

	/**
	 * Read the base path from the options
	 *
	 * @param InputInterface  $input  An InputInterface instance
	 * @param OutputInterface $output An OutputInterface instance
	 *
	 * @return  string  The base path
	 */
	protected function handleBasePath(InputInterface $input, OutputInterface $output): string
	{
		$path = realpath($input->getOption('basepath'));
		$this->writeln($output, 'Joomla! installation expected in ' . $path, OutputInterface::VERBOSITY_DEBUG);

		return $path;
	}

	/**
	 * Proxy for OutputInterface::writeln()
	 *
	 * @param OutputInterface $output An OutputInterface instance
	 * @param string|array    $message
	 * @param int             $level  One of OutputInterface::VERBOSITY_*
	 * @param int             $mode   One of OutputInterface::OUTPUT_*
	 *
	 * @return  void
	 */
	protected function writeln(OutputInterface $output, $message, $level = OutputInterface::VERBOSITY_NORMAL, $mode = OutputInterface::OUTPUT_NORMAL): void
	{
		if ($output->getVerbosity() >= $level)
		{
			$output->writeln($message, $mode);
		}
	}
}
