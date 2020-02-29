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

namespace GreenCape\JoomlaCLI\Command\Core;

use GreenCape\JoomlaCLI\Command;
use GreenCape\JoomlaCLI\Repository\VersionList;
use League\Flysystem\Adapter\Local;
use League\Flysystem\FileNotFoundException;
use League\Flysystem\Filesystem;
use RuntimeException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

/**
 * The Download command allows the installation of Joomla! from the command line.
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
     * Configure the options for the command
     *
     * @return  void
     */
    protected function configure(): void
    {
        $this
            ->setName('core:download')
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
            )
        ;
    }

    /**
     * Execute the command
     *
     * @param  InputInterface   $input   An InputInterface instance
     * @param  OutputInterface  $output  An OutputInterface instance
     *
     * @throws FileNotFoundException
     */
    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $this->output      = $output;
        $this->version     = $input->getArgument('version');
        $this->versionFile = $input->getOption('file');
        $this->cachePath   = $input->getOption('cache');

        $basePath = $input->getOption('basepath');

        $versionList = $this->getAvailableVersions();
        $this->createPath($this->cachePath);

        $tarball = $this->getTarball($versionList);
        $this->output->writeln("Archive is {$tarball}", OutputInterface::VERBOSITY_VERY_VERBOSE);

        $this->unpack($basePath, $tarball);

        $this->output->writeln("Installed Joomla! files to  {$basePath}", OutputInterface::VERBOSITY_VERY_VERBOSE);
    }

    /**
     * @return VersionList
     * @throws FileNotFoundException
     */
    private function getAvailableVersions(): VersionList
    {
        $filesystem = new Filesystem(new Local(dirname($this->versionFile)));
        $cacheFile  = basename($this->versionFile);

        return new VersionList($filesystem, $cacheFile);
    }

    private function createPath(string $path): void
    {
        if (!@mkdir($path, 0777, true) && !is_dir($path)) {
            throw new RuntimeException(sprintf('Directory "%s" could not be created', $path));  // @codeCoverageIgnore
        }
    }

    /**
     * @param  VersionList  $versions
     *
     * @return string
     */
    private function getTarball(VersionList $versions): string
    {
        $version   = $this->version;
        $cachePath = $this->cachePath;

        $requested = $version;
        $version   = $versions->resolve($version);
        $tarball   = $cachePath . '/' . $version . '.tar.gz';

        if (!$versions->isBranch($version) && file_exists($tarball)) {
            $this->output->writeln("$requested: Joomla! $version is already in cache",
                OutputInterface::VERBOSITY_VERBOSE);

            return $tarball;
        }

        if ($versions->isBranch($version)) {
            $this->output->writeln("$requested: Downloading Joomla! $version branch",
                OutputInterface::VERBOSITY_VERBOSE);
            $url = 'http://github.com/joomla/joomla-cms/tarball/' . $version;

            return $this->download($tarball, $url);
        }

        if ($versions->isTag($version)) {
            $this->output->writeln("$requested: Downloading Joomla! $version", OutputInterface::VERBOSITY_VERBOSE);

            try // to get the official release for that version
            {
                $this->output->writeln('Trying release channel', OutputInterface::VERBOSITY_VERY_VERBOSE);
                $url = "https://github.com/joomla/joomla-cms/releases/download/{$version}/Joomla_{$version}-Stable-Full_Package.tar.gz";

                return $this->download($tarball, $url);
            } catch (Throwable $exception) // else get it from the archive
            {
                $repository = $versions->getRepository($version);
                $this->output->writeln("Trying {$repository} archive", OutputInterface::VERBOSITY_VERY_VERBOSE);
                $url = 'https://github.com/' . $repository . '/archive/' . $version . '.tar.gz';

                return $this->download($tarball, $url);
            }
        }
        throw new RuntimeException("$requested: Version is unknown");
    }

    /**
     * @param          $basePath
     * @param  string  $tarball
     */
    private function unpack($basePath, string $tarball): void
    {
        $this->createPath($basePath);
        shell_exec("tar -zxvf {$tarball} -C {$basePath} --exclude-vcs");

        // If $basePath contains only a single directory, we need to lift everything up one level.
        $dirList = glob("{$basePath}/*", GLOB_ONLYDIR);

        if (count($dirList) === 1) {
            $subDir  = $dirList[0];
            $dirList = array_filter(
                glob("{$subDir}/{*,.*}", GLOB_NOSORT | GLOB_BRACE),
                static function ($filename) {
                    $basename = basename($filename);

                    return ($basename !== '.' && $basename !== '..');
                }
            );
            foreach ($dirList as $item) {
                shell_exec("mv {$item} {$basePath}");
            }
            shell_exec("rm -d {$subDir}");
        }
    }

    /**
     * @param  string  $filename
     * @param  string  $url
     *
     * @return string
     */
    private function download(string $filename, string $url): string
    {
        $bytes = file_put_contents($filename, @fopen($url, 'rb'));

        if ($bytes === false || $bytes === 0) {
            throw new RuntimeException("Failed to download $url");
        }

        return $filename;
    }
}
