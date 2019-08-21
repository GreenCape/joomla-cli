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

namespace GreenCape\JoomlaCLI\Command\Template;

use GreenCape\JoomlaCLI\Command;
use RuntimeException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * The override command creates a set of template and layout overrides for a Joomla! installation.
 * Joomla introduced template / layout overrides in version 1.5 (components and modules),
 * and extended it in version 2.5 (plugins) and in version 3 (layouts).
 *
 * @package     GreenCape\JoomlaCLI
 * @subpackage  Command
 * @since       Class available since Release 0.1.0
 * @see         http://docs.joomla.org/Understanding_Output_Overrides for version 1.5+
 * @see         http://docs.joomla.org/Layout_Overrides_in_Joomla for version 2.5+
 * @see         http://docs.joomla.org/J3.x:Sharing_layouts_across_views_or_extensions_with_JLayout for version 3+
 */
class OverrideCommand extends Command
{
	/**
	 * Configure the options for the overrides command
	 *
	 * @return  void
	 */
	protected function configure(): void
	{
		$this
			->setName('template:override')
			->setDescription('Creates template and layout overrides (Joomla! 1.5+)')
			->addOption(
				'force',
				'f',
				InputOption::VALUE_NONE,
				'Overwrite existing overrides in the template directory.'
			)
			->addArgument(
				'template',
				InputArgument::REQUIRED,
				'The system name of the template, e.g., `rhuk_milkyway`.'
			);
	}

	/**
	 * Execute the overrides command
	 *
	 * @param InputInterface  $input  An InputInterface instance
	 * @param OutputInterface $output An OutputInterface instance
	 *
	 * @return  void
	 */
	protected function execute(InputInterface $input, OutputInterface $output): void
	{
		$basePath = $input->getOption('basepath');
		$template = $input->getArgument('template');
		$force    = $input->getOption('force');

		$templateDir = $this->prepareTemplateDirectory($basePath . '/templates/' . $template, $output);

		$this->handleComponents($basePath, $templateDir, $force, $output);
		$this->handleModules($basePath, $templateDir, $force, $output);
		$this->handlePlugins($basePath, $templateDir, $force, $output);
		$this->handleLayouts($basePath, $templateDir, $force, $output);
	}

	/**
	 * @param string          $basePath
	 * @param string          $templateDir
	 * @param boolean         $force
	 * @param OutputInterface $output
	 */
	protected function handleComponents($basePath, $templateDir, $force, OutputInterface $output): void
	{
		foreach (glob($basePath . '/components/*') as $component)
		{
			if (!is_dir($component . '/views'))
			{
				continue;
			}
			$componentDir = basename($component);
			$output->writeln($componentDir, OutputInterface::VERBOSITY_VERY_VERBOSE);
			$this->safeCopyViews($component . '/views/*', $templateDir . '/' . $componentDir, $force, $output);
		}
	}

	/**
	 * @param                 $basePath
	 * @param                 $templateDir
	 * @param                 $force
	 * @param OutputInterface $output
	 */
	protected function handleModules($basePath, $templateDir, $force, OutputInterface $output): void
	{
		$this->safeCopyViews($basePath . '/modules/*', $templateDir, $force, $output);
	}

	/**
	 * @param                 $basePath
	 * @param                 $templateDir
	 * @param                 $force
	 * @param OutputInterface $output
	 */
	protected function handlePlugins($basePath, $templateDir, $force, OutputInterface $output): void
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
	protected function handleLayouts($basePath, $templateDir, $force, OutputInterface $output): void
	{
		$this->safeCopyRecursive($basePath . '/layouts', '*.php', $templateDir . '/layouts', $force, $output);
	}

	/**
	 * @param                 $templatePath
	 * @param OutputInterface $output
	 *
	 * @return string
	 */
	private function prepareTemplateDirectory($templatePath, OutputInterface $output): string
	{
		$templateDir = $templatePath . '/html';
		$output->writeln("Creating override views in $templateDir", OutputInterface::VERBOSITY_VERY_VERBOSE);

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
	private function safeCopy($source, $toDir, $force, OutputInterface $output): void
	{
		$filename = basename($source);
		$target   = $toDir . '/' . $filename;
		if ($force || !file_exists($target))
		{
			$output->writeln("Copying $source to $target", OutputInterface::VERBOSITY_DEBUG);
			copy($source, $target);
		}
	}

	/**
	 * @param                 $filePattern
	 * @param                 $toDir
	 * @param                 $force
	 * @param OutputInterface $output
	 */
	private function safeCopyDir($filePattern, $toDir, $force, OutputInterface $output): void
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
	private function safeMakeDir($dir, OutputInterface $output): void
	{
		if (!file_exists($dir))
		{
			$output->writeln("Creating directory $dir", OutputInterface::VERBOSITY_DEBUG);

			if (!mkdir($dir, 0775, true) && !is_dir($dir))
			{
				throw new RuntimeException(sprintf('Directory "%s" was not created', $dir)); // @codeCoverageIgnore
			}

			touch($dir . '/index.html');
		}
	}

	/**
	 * @param string          $pattern
	 * @param string          $toDir
	 * @param boolean         $force
	 * @param OutputInterface $output
	 */
	private function safeCopyViews($pattern, $toDir, $force, OutputInterface $output): void
	{
		foreach (glob($pattern) as $container)
		{
			if (!is_dir($container . '/tmpl'))
			{
				continue;
			}
			$dir = basename($container);
			$output->writeln($dir, OutputInterface::VERBOSITY_VERY_VERBOSE);
			$overlayDir = $toDir . '/' . $dir;
			$this->safeMakeDir($overlayDir, $output);
			$this->safeCopyDir($container . '/tmpl/*.php', $overlayDir, $force, $output);
		}
	}

	private function safeCopyRecursive($container, $pattern, $templateDir, $force, $output): void
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
}
