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
 * @since           File available since Release 0.1.0
 */

namespace GreenCape\JoomlaCLI;

use DirectoryIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

/**
 * Class Fileset
 *
 * @since  Class available since Release __DEPLOY_VERSION__
 */
class Fileset
{
    public const NO_RECURSE = 1;
    public const ONLY_DIRS  = 2;

    /**
     * @var string
     */
    private $dir;

    /**
     * @var array
     */
    private $files = [];

    /**
     * @var array
     */
    private $excludes = [
        '~(^|/)\.\.?$~',
    ];

    /**
     * Fileset constructor.
     *
     * @param  string  $dir
     */
    public function __construct(string $dir)
    {
        $this->dir = $dir;
    }

    /**
     * @param  string|array  $patterns
     *
     * @return Fileset
     */
    public function exclude(string $patterns): self
    {
        foreach ((array)$patterns as $pattern) {
            $this->excludes[] = $this->convertPattern($pattern);
        }

        return $this;
    }

    /**
     * @param  string  $pattern
     *
     * @return string
     */
    private function convertPattern(string $pattern): string
    {
        $pattern = str_replace(
            [
                '\\*\\*',
                '\\*',
            ],
            [
                '.*',
                '[^/]*',
            ],
            preg_quote($pattern, '~')
        );

        return "~^$pattern\$~";
    }

    /**
     * @return string
     */
    public function getDir(): string
    {
        return $this->dir;
    }

    /**
     * @return array
     */
    public function getFiles(): array
    {
        if (empty($this->files)) {
            $this->include('**');
        }

        $files = array_filter($this->files, function ($file) {
            foreach ($this->excludes as $pattern) {
                if (preg_match($pattern, $file)) {
                    return false;
                }
            }

            return true;
        });
        $files = array_values($files);

        return array_map(
            function ($file) {
                return "$this->dir/$file";
            },
            $files
        );
    }

    /**
     * @param  string|array  $patterns
     * @param  int           $flags
     *
     * @return Fileset
     */
    public function include($patterns, int $flags = 0): self
    {
        foreach ((array)$patterns as $pattern) {
            $pattern     = $this->convertPattern($pattern);
            $files       = array_merge(
                $this->files,
                $this->collectFiles($this->dir, $pattern, $flags)
            );
            $this->files = array_unique(
                $files
            );
        }

        return $this;
    }

    /**
     * @param  string  $dir
     * @param  string  $pattern
     *
     * @param  int     $flags
     *
     * @return array
     */
    private function collectFiles(string $dir, string $pattern, int $flags = 0): array
    {
        $files = [];

        if (!is_dir($dir)) {
            return $files; // @codeCoverageIgnore
        }

        $iterator = ($flags & self::NO_RECURSE) === self::NO_RECURSE
            ? new DirectoryIterator($dir)
            : new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($dir), RecursiveIteratorIterator::SELF_FIRST
            );
        $len      = strlen($dir) + 1;

        /** @var SplFileInfo $file */
        foreach ($iterator as $file) {
            if (($flags & self::ONLY_DIRS) === self::ONLY_DIRS && !$file->isDir()) {
                continue;
            }

            $pathname = substr($file->getPathname(), $len);

            if (preg_match($pattern, $pathname)) {
                $files[] = $pathname;
            }
        }

        return $files;
    }
}
