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
 * @subpackage      Driver
 * @author          Niels Braczek <nbraczek@bsds.de>
 * @copyright   (C) 2012-2019 GreenCape, Niels Braczek <nbraczek@bsds.de>
 * @license         http://opensource.org/licenses/MIT The MIT license (MIT)
 * @link            http://greencape.github.io
 * @since           File available since Release 0.1.0
 */

namespace GreenCape\JoomlaCLI\Driver;

use League\Flysystem\FileNotFoundException;
use League\Flysystem\Filesystem;

/**
 * Version specific methods
 *
 * @package     GreenCape\JoomlaCLI
 * @subpackage  Driver
 * @since       Class available since Release 0.1.1
 */
class Version
{
    /**
     * @var Filesystem The file system containing the Joomla! files
     */
    private $filesystem;

    /**
     * @var string[] Possible locations of the version file
     */
    private $locations = [
        '/libraries/src/Version.php', // J3.9
        '/libraries/cms/version/version.php', // J2.5, J3.5
        '/libraries/joomla/version.php', // J1.5
        '/includes/version.php', // J1.0, J1.7
    ];

    /** @var string[] The values from the version file */
    private $data = [];

    /**
     * Version constructor.
     *
     * @param  Filesystem  $filesystem  The file system containing the Joomla! files
     *
     * @throws FileNotFoundException
     */
    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;

        $content = $this->loadVersionFile();

        if (!is_string($content)) {
            throw new FileNotFoundException('Joomla! version file');
        }

        $prefix  = '(?:var\s*\$|public\s*\$|const\s*)';
        $var     = '([A-Z_]+)';
        $value   = '[\'"](.*?)[\'"];';
        $pattern = '~' . $prefix . '\s*' . $var . '\s*=\s*' . $value . '~';

        preg_match_all($pattern, $content, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $this->data[$match[1]] = $match[2];
        }
    }

    public function getRelease(): string
    {
        return $this->data['RELEASE'];
    }

    /**
     * @return string Short version format
     */
    public function getShortVersion(): string
    {
        return $this->data['RELEASE'] . '.' . $this->data['DEV_LEVEL'];
    }

    /**
     * @return string Long format version
     */
    public function getLongVersion(): string
    {
        return $this->data['PRODUCT'] . ' ' . $this->data['RELEASE'] . '.' . $this->data['DEV_LEVEL'] . ' '
               . $this->data['DEV_STATUS']
               . ' [ ' . $this->data['CODENAME'] . ' ] ' . $this->data['RELDATE'] . ' '
               . $this->data['RELTIME'] . ' ' . $this->data['RELTZ'];
    }

    /**
     * @return string|null
     * @throws FileNotFoundException
     */
    private function loadVersionFile(): ?string
    {
        foreach ($this->locations as $location) {
            if ($this->filesystem->has($location)) {
                return $this->filesystem->read($location);
            }
        }

        return null;
    }
}
