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
 * @author          Niels Braczek <nbraczek@bsds.de>
 * @copyright   (C) 2012-2019 GreenCape, Niels Braczek <nbraczek@bsds.de>
 * @license         http://opensource.org/licenses/MIT The MIT license (MIT)
 * @link            http://greencape.github.io
 * @since           File available since Release __DEPLOY_VERSION__
 */

namespace GreenCape\JoomlaCLI;

use RuntimeException;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Common Filesystem Methods
 *
 * Provides methods for accessing and manipulating the filesystem
 *
 * @property OutputInterface $output Must be provided by class using this trait
 *
 * @since  Class available since Release __DEPLOY_VERSION__
 */
trait FilesystemMethods
{
    /**
     * Apply a filter to files
     *
     * @param  Fileset|string  $fileset  A Fileset or a single filename to be filtered
     * @param  callable        $filter   A filter callback
     */
    protected function reflexive($fileset, callable $filter): void
    {
        if (is_string($fileset)) {
            $this->copyFile($fileset, $fileset, $filter);

            return;
        }

        foreach ($fileset->getFiles() as $file) {
            $this->copyFile($file, $file, $filter);
        }
    }

    /**
     * Checks if (every file from) target is newer than (every file from) source
     *
     * @param  Fileset|string  $target      The file(s) considered to be newer
     * @param  Fileset|string  ...$sources  The file(s) considered to be older
     *
     * @return bool
     */
    protected function isUptodate($target, ...$sources): bool
    {
        $targetFiles = is_string($target) ? [$target] : $target->getFiles();

        $targetTime = array_reduce(
            $targetFiles,
            static function ($carry, $file) {
                return min($carry, filemtime($file));
            },
            PHP_INT_MAX
        );

        if ($targetTime === PHP_INT_MAX) {
            $this->output->writeln(
                'Target files not found, so obviously not up to date',
                OutputInterface::VERBOSITY_DEBUG
            );

            return false;
        }

        foreach ($sources as $source) {
            $sourceFiles = is_string($source) ? [$source] : $source->getFiles();
            foreach ($sourceFiles as $file) {
                if (filemtime($file) > $targetTime) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Find the file matching the given version number
     *
     * This is used to locate (template) files suitable for a specific (Joomla) version.
     *
     * @param $pattern
     * @param $path
     * @param $version
     *
     * @return string|null
     */
    protected function versionMatch($pattern, $path, $version): ?string
    {
        $bestVersion = '0';
        $bestFile    = null;
        foreach (glob("$path/*") as $filename) {
            if (preg_match("~{$pattern}~", $filename, $match) && version_compare(
                    $bestVersion,
                    $match[1],
                    '<'
                ) && version_compare($match[1], $version, '<=')) {
                $bestVersion = $match[1];
                $bestFile    = $filename;
            }
        }

        return $bestFile;
    }

    /**
     * Create the directory specified by pathname
     *
     * This method will create nested directories specified in the pathname.
     * If `index` is true, it creates an empty `index.html` file in the directory
     *
     * @param  string  $dir    The directory path
     * @param  bool    $index  Create an empty `index.html` file
     */
    protected function mkdir(string $dir, bool $index = false): void
    {
        if (file_exists($dir) && !is_dir($dir)) {
            throw new RuntimeException("`{$dir}` exists but is not a directory");
        }

        $this->output->writeln("Creating directory <info>$dir</info>", OutputInterface::VERBOSITY_DEBUG);

        if (!@mkdir($dir, 0775, true) && !is_dir($dir)) {
            throw new RuntimeException("Directory `{$dir}` could not be created");  // @codeCoverageIgnore
        }

        if ($index) {
            touch($dir . '/index.html');
        }
    }

    /**
     * Delete files and directories
     *
     * @param  Fileset|string  $fileset  A Fileset or a single filename to be deleted
     */
    protected function delete($fileset): void
    {
        if (is_string($fileset)) {
            $this->deleteFile($fileset);

            return;
        }

        foreach ($fileset->getFiles() as $file) {
            $this->deleteFile($file);
        }
    }

    /**
     * Copy files with optional filtering
     *
     * @param  Fileset|string  $fileset  A Fileset or a single filename to be copied
     * @param  string          $to       The target directory for Filesets, target file for files
     * @param  callable|null   $filter   Optional filter callback
     */
    protected function copy($fileset, string $to, callable $filter = null): void
    {
        if (is_string($fileset)) {
            $this->copyFile($fileset, $to, $filter);

            return;
        }

        foreach ($fileset->getFiles() as $file) {
            $this->copyFile($file, str_replace($fileset->getDir(), $to, $file), $filter);
        }
    }

    /**
     * Download a file
     *
     * @param  string  $filename  The location to store the file
     * @param  string  $url       The URL where to get the file
     *
     * @return string The filename
     */
    protected function download(string $filename, string $url): string
    {
        $bytes = file_put_contents($filename, @fopen($url, 'rb'));

        if ($bytes === false || $bytes === 0) {
            throw new RuntimeException("Failed to download `{$url}`");
        }

        return $filename;
    }

    /**
     * Unpack a tar file
     *
     * @param  string  $toDir  The directory for the unpacked archive
     * @param  string  $file   The archive file
     */
    protected function untar(string $toDir, string $file): void
    {
        $this->mkdir($toDir);
        exec("tar -zxvf {$file} -C {$toDir} --exclude-vcs");

        // If $toDir contains only a single directory, we need to lift everything up one level.
        $dirList = glob("{$toDir}/*", GLOB_ONLYDIR);

        if (count($dirList) === 1) {
            $this->copy(
                new Fileset($dirList[0]),
                $toDir
            );

            $this->delete($dirList[0]);
        }
    }

    /**
     * Copy a file with optional filtering
     *
     * @param  string         $file    The file to be copied
     * @param  string         $toFile  The filename of the copy
     * @param  callable|null  $filter  Optional filter callback
     */
    private function copyFile(string $file, string $toFile, callable $filter = null): void
    {
        if (is_dir($file)) {
            return;
        }

        $this->output->writeln(
            "Copying <info>{$file}</info>" . ($filter !== null ? ' with filter' : '') . " to <info>{$toFile}</info>",
            OutputInterface::VERBOSITY_DEBUG
        );

        $content = file_get_contents($file);

        if (is_callable($filter)) {
            $content = $filter($content);
        }

        $this->mkdir(dirname($toFile));
        file_put_contents($toFile, $content);
    }

    /**
     * Delete a file or a directory
     *
     * If  the specified file is a directory, unlike `rmdir()`,
     * the directory does not need to be empty.
     *
     * Non-existing file is just ignored.
     *
     * @param  string  $file  The file or directory to be deleted
     */
    private function deleteFile($file): void
    {
        if (!file_exists($file)) {
            return;
        }

        $this->output->writeln("Deleting <info>{$file}</info>", OutputInterface::VERBOSITY_DEBUG);

        passthru(is_dir($file) ? "rm -rf $file" : "rm $file");
    }
}
