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
 * @since       File available since Release 0.1.1
 */

namespace GreenCape\JoomlaCLI;

use GreenCape\Manifest\FileManifest;
use GreenCape\Manifest\FileSection;
use GreenCape\Manifest\Manifest;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * The Install command allows the installation of Joomla! extensions from the command line.
 *
 * @package         GreenCape\JoomlaCLI
 * @subpackage      Command
 * @author          Niels Braczek <nbraczek@bsds.de>
 * @copyright   (C) 2012-2014 GreenCape, Niels Braczek <nbraczek@bsds.de>
 * @license         http://opensource.org/licenses/GPL-2.0 GNU General Public License, version 2.0 (GPLv2)
 * @link            http://www.greencape.com/
 * @since           Class available since Release 0.2.0
 */
class ExtractCommand extends Command
{
	/** @var string The root of the Joomla! installation. Defaults to the current working directory. */
	private $basePath = '.';

	/** @var string The type of extension, one of 'plugin', 'module', 'component', or 'template' */
	private $type = null;

	/** @var string The name of the extension */
	private $extension = null;

	/**
	 * Configure the options for the install command
	 *
	 * @return  void
	 */
	protected function configure()
	{
		$this
			->setName('extract')
			->setDescription('Extract a Joomla! extension into an installable package.')
			->addOption(
				'type',
				't',
				InputOption::VALUE_OPTIONAL,
				'Determine the type of the extension. If omitted, the type is guessed from the extension name.'
			)
			->addOption(
				'dir',
				'd',
				InputOption::VALUE_OPTIONAL,
				'The destination directory.'
			)
			->addArgument(
				'extension',
				InputArgument::REQUIRED,
				'The name of the extension.'
			);
	}

	/**
	 * Execute the extract command
	 *
	 * @param   InputInterface  $input  An InputInterface instance
	 * @param   OutputInterface $output An OutputInterface instance
	 *
	 * @return  integer  0 if everything went fine, 1 on error
	 */
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$this->setupEnvironment('site', $input, $output);

		$this->basePath  = $input->getOption('basepath');
		$this->extension = $input->getArgument('extension');
		$this->type      = $this->getType($input->getOption('type'), $this->extension);
		$dir             = $input->getOption('dir');

		if (empty($this->type))
		{
			$output->writeln('Could not determine type. Use --type to specify.');

			return 1;
		}

		try
		{
			$manifest = $this->getManifest();

			$this->processFiles($manifest, $dir);
		} catch (\Exception $e)
		{
			$output->writeln($e->getMessage());

			return 1;
		}

		return 0;
	}

	/**
	 * Get the type of the extension
	 *
	 * @param string $type
	 * @param string $extension
	 *
	 * @return mixed
	 */
	public function getType($type = null, $extension = null)
	{
		if (empty($extension))
		{
			return $this->type;
		}

		$type = strtolower($type);
		if (empty($type))
		{
			$types  = array(
				'plg' => 'plugin',
				'mod' => 'module',
				'com' => 'component',
				'tpl' => 'template',
			);
			$prefix = substr($extension, 0, 3);
			if (isset($types[$prefix]))
			{
				$type = $types[$prefix];
			}
		}

		return $type;
	}

	/**
	 * Get the manifest
	 *
	 * @throws \Exception
	 * @return Manifest
	 */
	public function getManifest()
	{
		$path = null;
		$file = null;
		if ($this->type == 'plugin')
		{
			$path = $this->basePath . '/' . str_replace('_', '/', str_replace('plg_', 'plugins/', $this->extension));
			$file = $path . '/' . basename($path) . '.xml';
		}
		elseif ($this->type == 'module')
		{
			$path = $this->basePath . '/modules/' . $this->extension;
			$file = $path . '/' . basename($path) . '.xml';
		}
		elseif ($this->type == 'template')
		{
			$path = $this->basePath . '/' . str_replace('tpl_', 'templates/', $this->extension);
			$file = $path . '/templateDetails.xml';
		}
		elseif ($this->type == 'admin-module')
		{
			$path = $this->basePath . '/administrator/modules/' . $this->extension;
			$file = $path . '/' . basename($path) . '.xml';
		}
		elseif ($this->type == 'admin-template')
		{
			$path = $this->basePath . '/administrator/' . str_replace('tpl_', 'templates/', $this->extension);
			$file = $path . '/templateDetails.xml';
		}
		elseif ($this->type == 'component')
		{
			$path = $this->basePath . '/administrator/components/' . $this->extension;
			$file = $path . '/' . str_replace('com_', '', basename($path)) . '.xml';
		}
		else
		{
			throw new \Exception("Extract does not support {$this->type} extensions.");
		}
		if (!file_exists($path))
		{
			throw new \Exception("Path {$path} does not exist.");
		}
		if (!file_exists($file))
		{
			throw new \Exception("Manifest {$file} not found.");
		}
		$manifest = Manifest::load($file);

		return $manifest;
	}

	/**
	 * @param FileManifest $manifest
	 * @param              $dir
	 */
	protected function processFiles($manifest, $dir)
	{
		/** @var FileSection $files */
		$files = $manifest->getSection('files');
		$manifest->removeSection('files');
		$base    = $files->getBase();
		$destDir = $dir . '/' . $base;
		mkdir($destDir, 0777, true);
		$files = new FileSection();
		$files->setBase($base);
	}
}
