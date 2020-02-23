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

namespace GreenCape\JoomlaCLI\Command\Extension;

use Exception;
use GreenCape\JoomlaCLI\Command;
use JInstaller;
use JInstallerHelper;
use RuntimeException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * The Install command allows the installation of Joomla! extensions from the command line.
 *
 * @package     GreenCape\JoomlaCLI
 * @subpackage  Command
 * @since       Class available since Release 0.1.0
 */
class InstallCommand extends Command
{
    /**
     * Configure the options for the command
     *
     * @return  void
     */
    protected function configure(): void
    {
        $this
            ->setName('extension:install')
            ->setDescription('Installs a Joomla! extension')
            ->addArgument(
                'extension',
                InputArgument::REQUIRED,
                'The path to the extension.'
            )
        ;
    }

    /**
     * Execute the command
     *
     * @param  InputInterface   $input   An InputInterface instance
     * @param  OutputInterface  $output  An OutputInterface instance
     *
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $this->setupEnvironment('administrator', $input, $output);

        // Enable debug, so that JInstaller::install() throws exceptions on problems
        $this->joomla->setCfg('debug', 1);

        $installer = JInstaller::getInstance();

        if (!$installer->install($this->handleExtension($input, $output))) {
            $output->writeln($this->getExtensionInfo($installer));

            return;
        }

        $output->writeln('Installation failed due to unknown reason.');
    }

    /**
     * Handle the specified extension
     * An extension can be provided as a download, a directory, or an archive.
     * This method prepares the installation by providing the extension in a
     * temporary directory ready for install.
     *
     * @param  InputInterface   $input   An InputInterface instance
     * @param  OutputInterface  $output  An OutputInterface instance
     *
     * @return  string  The location of the prepared extension
     */
    protected function handleExtension(InputInterface $input, OutputInterface $output): string
    {
        $source = $input->getArgument('extension');

        if (strpos($source, '://')) {
            $tmpPath = $this->handleDownload($output, $source);
        } elseif (is_dir($source)) {
            $tmpPath = $this->handleDirectory($output, $source);
        } else {
            $tmpPath = $this->handleArchive($output, $source);
        }

        return $tmpPath;
    }

    /**
     * Get information about the installed extension
     *
     * @param  JInstaller  $installer
     *
     * @return  array  A message array suitable for OutputInterface::write[ln]
     */
    private function getExtensionInfo($installer): array
    {
        $manifest = $installer->getManifest();
        $data     = $this->joomla->getExtensionInfo($manifest);

        $message = [
            'Installed ' . $data['type'] . ' <info>' . $data['name'] . '</info> version <info>' . $data['version'] . '</info>',
            '',
            wordwrap($data['description'], 60),
            '',
        ];

        return $message;
    }

    /**
     * Prepare the installation for an extension specified by a URL
     *
     * @param  OutputInterface  $output  An OutputInterface instance
     * @param  string           $source  The extension source
     *
     * @return  string  The location of the prepared extension
     */
    private function handleDownload(OutputInterface $output, $source): string
    {
        $output->writeln("Downloading $source", OutputInterface::VERBOSITY_VERBOSE);

        return $this->unpack(JInstallerHelper::downloadPackage($source));
    }

    /**
     * Prepare the installation for an extension specified by a directory
     *
     * @param  OutputInterface  $output  An OutputInterface instance
     * @param  string           $source  The extension source
     *
     * @return  string  The location of the prepared extension
     */
    private function handleDirectory(OutputInterface $output, $source): string
    {
        $tmpDir  = $this->joomla->getCfg('tmp_path');
        $tmpPath = $tmpDir . '/' . uniqid('install_', true);
        $output->writeln("Copying $source", OutputInterface::VERBOSITY_VERBOSE);

        if (!mkdir($tmpPath) && !is_dir($tmpPath)) {
            throw new RuntimeException(sprintf('Directory "%s" was not created', $tmpPath));
        }

        copy($source, $tmpPath);

        return $tmpPath;
    }

    /**
     * Prepare the installation for an extension in an archive
     *
     * @param  OutputInterface  $output  An OutputInterface instance
     * @param  string           $source  The extension source
     *
     * @return  string  The location of the prepared extension
     */
    private function handleArchive(OutputInterface $output, $source): string
    {
        $tmpDir  = $this->joomla->getCfg('tmp_path');
        $tmpPath = $tmpDir . '/' . basename($source);
        $output->writeln("Extracting $source", OutputInterface::VERBOSITY_VERBOSE);

        copy($source, $tmpPath);

        return $this->unpack($tmpPath);
    }

    /**
     * Unpack an extension archive
     *
     * @param  string  $tmpPath  The location of the archive
     *
     * @return  string  The location of the unpacked extension
     */
    private function unpack($tmpPath): string
    {
        $result  = JInstallerHelper::unpack($tmpPath);
        $tmpPath = $result['extractdir'];

        return $tmpPath;
    }
}
