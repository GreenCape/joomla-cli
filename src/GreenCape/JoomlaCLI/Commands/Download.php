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
 * @since           File available since Release 0.1.1
 */

namespace GreenCape\JoomlaCLI;

use RuntimeException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

/**
 * The Dowmload command allows the installation of Joomla! from the command line.
 *
 * @package     GreenCape\JoomlaCLI
 * @subpackage  Command
 * @since       Class available since Release 0.1.1
 */
class DownloadCommand extends Command
{
	/**
	 * @var string
	 */
	private $versionFile;

	/**
	 * @var string
	 */
	private $version;

	/**
	 * @var string
	 */
	private $cachePath;

	/**
	 * @var OutputInterface
	 */
	private $output;

	/**
	 * Configure the options for the install command
	 *
	 * @return  void
	 */
	protected function configure(): void
	{
		$this
			->setName('download')
			->setDescription('Downloads a Joomla! version and unpacks it to the base path')
			->addArgument(
				'version',
				InputArgument::OPTIONAL,
				'The Joomla! version to install.',
				'latest'
			)
			->addOption(
				'file',
				'f',
				InputArgument::OPTIONAL,
				'Location of the version cache file',
				'/tmp/versions.json'
			)
			->addOption(
				'cache',
				'c',
				InputArgument::OPTIONAL,
				'Location of the cache for Joomla! packages',
				'.cache'
			);
	}

	/**
	 * Execute the setup command
	 *
	 * @param InputInterface  $input  An InputInterface instance
	 * @param OutputInterface $output An OutputInterface instance
	 *
	 * @return  integer  0 if everything went fine, 1 on error
	 */
	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$this->output      = $output;
		$this->version     = $input->getArgument('version');
		$this->versionFile = $input->getOption('file');
		$this->cachePath   = $input->getOption('cache');

		try
		{
			$basePath = $input->getOption('basepath');

			$this->getAvailableVersions();
			$this->createPath($this->cachePath);

			$tarball = $this->getTarball();
			$this->output->writeln("Archive is {$tarball}", OutputInterface::VERBOSITY_VERY_VERBOSE);

			$this->createPath($basePath);
			shell_exec("tar -zxvf {$tarball} -C {$basePath} --exclude-vcs");

			$this->output->writeln("Installed Joomla! files to  {$basePath}", OutputInterface::VERBOSITY_VERY_VERBOSE);

			return 0;
		}
		catch (Throwable $e)
		{
			$this->output->writeln($e->getMessage());

			return 1;
		}
	}

	/**
	 * @return string
	 */
	private function getTarball(): string
	{
		$versionFile = $this->versionFile;
		$version     = $this->version;
		$cachePath   = $this->cachePath;

		$versions  = json_decode(file_get_contents($versionFile), true);
		$requested = $version;

		// Resolve alias
		if (isset($versions['alias'][$version]))
		{
			$version = $versions['alias'][$version];
		}

		$tarball = $cachePath . '/' . $version . '.tar.gz';

		if (!isset($versions['heads'][$version]) && file_exists($tarball))
		{
			$this->output->writeln("$requested: Joomla $version is already in cache", OutputInterface::VERBOSITY_VERBOSE);

			return $tarball;
		}

		if (isset($versions['heads'][$version]))
		{
			// It's a branch, so get it from the original repo
			$url = 'http://github.com/joomla/joomla-cms/tarball/' . $version;
		}
		elseif (isset($versions['tags'][$version]))
		{
			if (version_compare($version, '2.0', '>'))
			{
				$url = "https://github.com/joomla/joomla-cms/releases/download/{$version}/Joomla_{$version}-Stable-Full_Package.tar.gz";
			}
			else
			{
				$url = 'https://github.com/' . $versions['tags'][$version] . '/archive/' . $version . '.tar.gz';
			}
		}
		else
		{
			throw new RuntimeException("$requested: Version is unknown");
		}

		$this->output->writeln("$requested: Downloading Joomla $version", OutputInterface::VERBOSITY_VERBOSE);
		$bytes = file_put_contents($tarball, fopen($url, 'rb'));

		if ($bytes === false || $bytes === 0)
		{
			throw new RuntimeException("$requested: Failed to download $url");
		}

		return $tarball;
	}

	private function getAvailableVersions(): void
	{
		// GreenCape first, so entries get overwritten if provided by Joomla
		$repos = array(
			'greencape/joomla-legacy',
			'joomla/joomla-cms',
		);

		if (file_exists($this->versionFile) && time() - filemtime($this->versionFile) < 86400)
		{
			return;
		}

		$versions = array();
		foreach ($repos as $repo)
		{
			$command = "git ls-remote https://github.com/{$repo}.git | grep -E 'refs/(tags|heads)' | grep -v '{}'";
			$result  = shell_exec($command);
			$refs    = explode(PHP_EOL, $result);
			$pattern = '/^[0-9a-f]+\s+refs\/(heads|tags)\/([a-z0-9\.\-_]+)$/im';
			foreach ($refs as $ref)
			{
				if (preg_match($pattern, $ref, $match))
				{
					if ($match[1] === 'tags')
					{
						if (!preg_match('/^\d+\.\d+\.\d+$/m', $match[2]))
						{
							continue;
						}
						$parts = explode('.', $match[2]);
						$this->checkAlias($versions, $parts[0], $match[2]);
						$this->checkAlias($versions, $parts[0] . '.' . $parts[1], $match[2]);
						$this->checkAlias($versions, 'latest', $match[2]);
					}
					$versions[$match[1]][$match[2]] = $repo;
				}
			}
		}

		// Special case: 1.6 and 1.7 belong to 2.x
		$versions['alias']['1'] = $versions['alias']['1.5'];

		file_put_contents($this->versionFile, json_encode($versions, JSON_PRETTY_PRINT));
	}

	/**
	 * @param $versions
	 * @param $alias
	 * @param $version
	 *
	 * @return void
	 */
	private function checkAlias(&$versions, $alias, $version): void
	{
		if (!isset($versions['alias'][$alias]) || version_compare($versions['alias'][$alias], $version, '<'))
		{
			$versions['alias'][$alias] = $version;
		}
	}

	private function createPath(string $path): void
	{
		if (!@mkdir($path, 0777, true) && !is_dir($path))
		{
			throw new RuntimeException(sprintf('Directory "%s" could not be created', $path));
		}
	}
}
