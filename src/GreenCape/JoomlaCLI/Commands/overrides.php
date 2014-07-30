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
 * @since       File available since Release 0.2.0
 */

namespace GreenCape\JoomlaCLI;

use Symfony\Component\Console\Command\Command as BaseCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * The overrides command creates a set of template and layout overrides for a Joomla! installation.
 * Joomla introduced template / layout overrides in version 1.5 (components and modules),
 * and extended it in version 2.5 (plugins) and in version 3 (layouts).
 *
 * @package     GreenCape\JoomlaCLI
 * @subpackage  Command
 * @author      Niels Braczek <nbraczek@bsds.de>
 * @copyright   (C) 2012-2014 GreenCape, Niels Braczek <nbraczek@bsds.de>
 * @license     http://opensource.org/licenses/GPL-2.0 GNU General Public License, version 2.0 (GPLv2)
 * @link        http://www.greencape.com/
 * @since       Class available since Release 0.2.0
 * @see         http://docs.joomla.org/Understanding_Output_Overrides for version 1.5+
 * @see         http://docs.joomla.org/Layout_Overrides_in_Joomla for version 2.5+
 * @see         http://docs.joomla.org/J3.x:Sharing_layouts_across_views_or_extensions_with_JLayout for version 3+
 */
class OverridesCommand extends Command
{
	/**
	 * Configure the options for the overrides command
	 *
	 * @return  void
	 */
	protected function configure()
	{
		$this
			->setName('overrides')

			->setDescription('Create template and layout overrides')

			->addOption(
			'force',
				'f',
				InputOption::VALUE_NONE,
				'Overwrite existing overrides in the template directory.'
			)

			->addArgument(
			'template',
				InputArgument::REQUIRED,
				'The path to the template, relative to the base path.'
			);
	}

	/**
	 * Execute the overrides command
	 *
	 * @param   InputInterface  $input  An InputInterface instance
	 * @param   OutputInterface $output An OutputInterface instance
	 *
	 * @return  void
	 */
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$this->setupEnvironment('site', $input, $output);

		$basePath = $input->getOption('basepath');
		$template = $input->getArgument('template');
		$force    = $input->getOption('force');

		$templateDir = $this->prepareTemplateDirectory($basePath . '/' . $template, $output);

		$this->handleComponents($basePath, $templateDir, $force, $output);
		$this->handleModules($basePath, $templateDir, $force, $output);
		$this->handlePlugins($basePath, $templateDir, $force, $output);
		$this->handleLayouts($basePath, $templateDir, $force, $output);
	}

	/**
	 * @param                 $templatePath
	 * @param OutputInterface $output
	 *
	 * @return string
	 */
	private function prepareTemplateDirectory($templatePath, OutputInterface $output)
	{
		$templateDir = $templatePath . '/html';
		$this->writeln($output, "Creating override views in $templateDir", OutputInterface::VERBOSITY_VERY_VERBOSE);

		$this->safeMakeDir($templateDir, $output);

		return $templateDir;
	}

	/**
	 * @param                 $source
	 * @param                 $toDir
	 * @param                 $force
	 * @param OutputInterface $output
	 *
	 * @return void
	 */
	private function safeCopy($source, $toDir, $force, OutputInterface $output)
	{
		$filename = basename($source);
		$target   = $toDir . '/' . $filename;
		if (!file_exists($target) || $force)
		{
			$this->writeln($output, "Copying $source to $target", OutputInterface::VERBOSITY_DEBUG);
			copy($source, $target);
		}
	}

	/**
	 * @param                 $filePattern
	 * @param                 $toDir
	 * @param                 $force
	 * @param OutputInterface $output
	 */
	private function safeCopyDir($filePattern, $toDir, $force, OutputInterface $output)
	{
		foreach (glob($filePattern) as $file)
		{
			$this->safeCopy($file, $toDir, $force, $output);
		}
	}

	/**
	 * @param                 $dir
	 * @param OutputInterface $output
	 */
	private function safeMakeDir($dir, OutputInterface $output)
	{
		if (!file_exists($dir))
		{
			$this->writeln($output, "Creating directory $dir", OutputInterface::VERBOSITY_DEBUG);
			mkdir($dir, 0775, true);
			touch($dir . '/index.html');
		}
	}

	/**
	 * @param                 $pattern
	 * @param                 $toDir
	 * @param                 $force
	 * @param OutputInterface $output
	 */
	private function safeCopyViews($pattern, $toDir, $force, OutputInterface $output)
	{
		foreach (glob($pattern) as $container)
		{
			if (!is_dir($container . '/tmpl'))
			{
				continue;
			}
			$dir = basename($container);
			$this->writeln($output, $dir, OutputInterface::VERBOSITY_VERY_VERBOSE);
			$overlayDir = $toDir . '/' . $dir;
			$this->safeMakeDir($overlayDir, $output);
			$this->safeCopyDir($container . '/tmpl/*.php', $overlayDir, $force, $output);
		}
	}

	private function safeCopyRecursive($container, $pattern, $templateDir, $force, $output)
	{
		foreach (glob($container . '/*') as $entry)
		{
			if (is_dir($entry))
			{
				$targetDir = $templateDir . '/' . basename($entry);
				$this->safeMakeDir($targetDir, $output);
				$this->safeCopyRecursive($entry, $pattern, $targetDir, $force, $output);
			}
			elseif (fnmatch($pattern, basename($entry)))
			{
				$this->safeCopy($entry, $templateDir, $force, $output);
			}
		}
	}

	/**
	 * @param                 $basePath
	 * @param                 $templateDir
	 * @param                 $force
	 * @param OutputInterface $output
	 */
	protected function handleComponents($basePath, $templateDir, $force, OutputInterface $output)
	{
		foreach (glob($basePath . '/components/*') as $component)
		{
			if (!is_dir($component . '/views'))
			{
				continue;
			}
			$componentDir = basename($component);
			$this->writeln($output, $componentDir, OutputInterface::VERBOSITY_VERY_VERBOSE);
			$this->safeCopyViews($component . '/views/*', $templateDir . '/' . $componentDir, $force, $output);
		}
	}

	/**
	 * @param                 $basePath
	 * @param                 $templateDir
	 * @param                 $force
	 * @param OutputInterface $output
	 */
	protected function handleModules($basePath, $templateDir, $force, OutputInterface $output)
	{
		$this->safeCopyViews($basePath . '/modules/*', $templateDir, $force, $output);
	}

	/**
	 * @param                 $basePath
	 * @param                 $templateDir
	 * @param                 $force
	 * @param OutputInterface $output
	 */
	protected function handlePlugins($basePath, $templateDir, $force, OutputInterface $output)
	{
		foreach (glob($basePath . '/plugins/*') as $pluginType)
		{
			foreach (glob($pluginType . '/*') as $container)
			{
				if (!is_dir($container . '/tmpl'))
				{
					continue;
				}
				$dir        = 'plg_' . basename($pluginType) . '_' . basename($container);
				$overlayDir = $templateDir . '/' . $dir;
				$this->safeMakeDir($overlayDir, $output);
				$this->safeCopyDir($container . '/tmpl/*.php', $overlayDir, $force, $output);
			}
		}
	}

	/**
	 * @param                 $basePath
	 * @param                 $templateDir
	 * @param                 $force
	 * @param OutputInterface $output
	 */
	protected function handleLayouts($basePath, $templateDir, $force, OutputInterface $output)
	{
		$this->safeCopyRecursive($basePath . '/layouts', '*.php', $templateDir . '/layouts', $force, $output);
	}
}
