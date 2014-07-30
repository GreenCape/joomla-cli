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

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * The Install command allows the installation of Joomla! extensions from the command line.
 *
 * @package     GreenCape\JoomlaCLI
 * @subpackage  Command
 * @author      Niels Braczek <nbraczek@bsds.de>
 * @copyright   (C) 2012-2014 GreenCape, Niels Braczek <nbraczek@bsds.de>
 * @license     http://opensource.org/licenses/GPL-2.0 GNU General Public License, version 2.0 (GPLv2)
 * @link        http://www.greencape.com/
 * @since       File available since Release 1.0.0
 */
class InstallCommand extends Command
{
	/**
	 * Configure the options for the install command
	 *
	 * @return  void
	 */
	protected function configure()
	{
		$this
			->setName('install')

			->setDescription('Install a Joomla! extension')

			->addArgument(
			'extension',
				InputArgument::REQUIRED,
				'The path to the extension.'
			);
	}

	/**
	 * Execute the install command
	 *
	 * @param   InputInterface  $input  An InputInterface instance
	 * @param   OutputInterface $output An OutputInterface instance
	 *
	 * @return  integer  0 if everything went fine, 1 on error
	 */
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$this->setupEnvironment('administrator', $input, $output);

		// Enable debug, so that JInstaller::install() throws exceptions on problems
		$this->joomla->setCfg('debug', 1);

		$installer = \JInstaller::getInstance();

		if ($installer->install($this->handleExtension($input, $output)))
		{
			$output->writeln($this->getExtensionInfo($installer));

			return 0;
		}
		else
		{
			$output->writeln('Installation failed due to unknown reason.');

			return 1;
		}
	}

	/**
	 * Handle the specified extension
	 * An extension can be provided as a download, a directory, or an archive.
	 * This method prepares the installation by providing the extension in a
	 * temporary directory ready for install.
	 *
	 * @param   InputInterface  $input  An InputInterface instance
	 * @param   OutputInterface $output An OutputInterface instance
	 *
	 * @return  string  The location of the prepared extension
	 */
	protected function handleExtension(InputInterface $input, OutputInterface $output)
	{
		$source = $input->getArgument('extension');

		if (strpos($source, '://'))
		{
			$tmpPath = $this->handleDownload($output, $source);
		}
		elseif (is_dir($source))
		{
			$tmpPath = $this->handleDirectory($output, $source);
		}
		else
		{
			$tmpPath = $this->handleArchive($output, $source);
		}

		return $tmpPath;
	}

	/**
	 * Get information about the installed extension
	 *
	 * @param   \JInstaller $installer
	 *
	 * @return  array  A message array suitable for OutputInterface::write[ln]
	 */
	private function getExtensionInfo($installer)
	{
		$manifest = $installer->getManifest();
		$data     = $this->joomla->getExtensionInfo($manifest);

		$message = array(
			'Installed ' . $data['type'] . ' <info>' . $data['name'] . '</info> version <info>' . $data['version'] . '</info>',
			'',
			wordwrap($data['description'], 60),
			''
		);

		return $message;
	}

	/**
	 * Prepare the installation for an extension specified by a URL
	 *
	 * @param   OutputInterface $output An OutputInterface instance
	 * @param   string          $source The extension source
	 *
	 * @return  string  The location of the prepared extension
	 */
	private function handleDownload(OutputInterface $output, $source)
	{
		$this->writeln($output, "Downloading $source", OutputInterface::VERBOSITY_VERBOSE);

		return $this->unpack(\JInstallerHelper::downloadPackage($source));
	}

	/**
	 * Prepare the installation for an extension specified by a directory
	 *
	 * @param   OutputInterface $output An OutputInterface instance
	 * @param   string          $source The extension source
	 *
	 * @return  string  The location of the prepared extension
	 */
	private function handleDirectory(OutputInterface $output, $source)
	{
		$tmpDir  = $this->joomla->getCfg('tmp_path');
		$tmpPath = $tmpDir . '/' . uniqid('install_');
		$this->writeln($output, "Copying $source", OutputInterface::VERBOSITY_VERBOSE);

		mkdir($tmpPath);
		copy($source, $tmpPath);

		return $tmpPath;
	}

	/**
	 * Prepare the installation for an extension in an archive
	 *
	 * @param   OutputInterface $output An OutputInterface instance
	 * @param   string          $source The extension source
	 *
	 * @return  string  The location of the prepared extension
	 */
	private function handleArchive(OutputInterface $output, $source)
	{
		$tmpDir  = $this->joomla->getCfg('tmp_path');
		$tmpPath = $tmpDir . '/' . basename($source);
		$this->writeln($output, "Extracting $source", OutputInterface::VERBOSITY_VERBOSE);

		copy($source, $tmpPath);

		return $this->unpack($tmpPath);
	}

	/**
	 * Unpack an extension archive
	 *
	 * @param   string $tmpPath The location of the archive
	 *
	 * @return  string  The location of the unpacked extension
	 */
	private function unpack($tmpPath)
	{
		$result  = \JInstallerHelper::unpack($tmpPath);
		$tmpPath = $result['extractdir'];

		return $tmpPath;
	}
}
