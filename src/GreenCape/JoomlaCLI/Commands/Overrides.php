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
