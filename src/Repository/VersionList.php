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

namespace GreenCape\JoomlaCLI\Repository;

use League\Flysystem\FileNotFoundException;
use League\Flysystem\Filesystem;

/**
 * The abstract command provides common methods for most JoomlaCLI commands.
 *
 * @since  Class available since Release 0.1.1
 */
class VersionList
{
    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var string
     */
    private $versionFile;

    /**
     * @var array
     */
    private $versions = [];

    /**
     * VersionList constructor.
     *
     * @param  Filesystem  $filesystem
     * @param  string      $cacheFile
     *
     * @throws FileNotFoundException
     */
    public function __construct(Filesystem $filesystem, string $cacheFile)
    {
        $this->filesystem  = $filesystem;
        $this->versionFile = $cacheFile;

        $this->init();
    }

    /**
     * @param  string  $version
     *
     * @return string
     */
    public function resolve(string $version): string
    {
        if (isset($this->versions['alias'][$version])) {
            $version = $this->versions['alias'][$version];
        }

        return $version;
    }

    /**
     * @param  string  $version
     *
     * @return bool
     */
    public function isBranch(string $version): bool
    {
        return isset($this->versions['heads'][$version]);
    }

    /**
     * @param  string  $version
     *
     * @return bool
     */
    public function isTag(string $version): bool
    {
        return isset($this->versions['tags'][$version]);
    }

    public function getRepository(string $version)
    {
        return $this->versions['tags'][$version];
    }

    /**
     * @return void
     * @throws FileNotFoundException
     */
    private function init(): void
    {
        // GreenCape first, so entries get overwritten if provided by Joomla
        $repos = [
            'greencape/joomla-legacy',
            'joomla/joomla-cms',
        ];

        if ($this->filesystem->has($this->versionFile) && (time() - $this->filesystem->getTimestamp($this->versionFile) < 86400)) {
            $this->versions = json_decode($this->filesystem->read($this->versionFile), true);

            return;
        }

        $versions = [];
        foreach ($repos as $repo) {
            $command = "git ls-remote https://github.com/{$repo}.git | grep -E 'refs/(tags|heads)' | grep -v '{}'";
            $result  = shell_exec($command);
            $refs    = explode(PHP_EOL, $result);
            $pattern = '/^[0-9a-f]+\s+refs\/(heads|tags)\/([a-z0-9\.\-_]+)$/im';

            foreach ($refs as $ref) {
                if (preg_match($pattern, $ref, $match)) {
                    if ($match[1] === 'tags') {
                        if (!preg_match('/^\d+\.\d+\.\d+$/m', $match[2])) {
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

        $this->versions = $versions;

        $this->filesystem->put($this->versionFile, json_encode($versions, JSON_PRETTY_PRINT));
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
        if (!isset($versions['alias'][$alias]) || version_compare($versions['alias'][$alias], $version, '<')) {
            $versions['alias'][$alias] = $version;
        }
    }
}
