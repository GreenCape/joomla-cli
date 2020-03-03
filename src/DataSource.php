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

namespace GreenCape\JoomlaCLI;

/**
 * The abstract command provides common methods for most JoomlaCLI commands.
 *
 * @package     GreenCape\JoomlaCLI
 * @since       Class available since Release 0.2.0
 */
class DataSource
{
    const DSN_PATTERN = '~(?:(\w+)(?::(\w+))?@)?((?:\w+://)?[\w.]+)(?::(\d+))?(?:/(\w+))?~';

    private $user;
    private $pass;
    private $host;
    private $port;
    private $base;

    public function __construct($dsn, $default = null)
    {
        [$this->user, $this->pass, $this->host, $this->port, $this->base] = $this->extract($dsn, $default);
    }

    /**
     * @param        $dsn
     * @param  null  $default
     *
     * @return array
     */
    private function extract($dsn, $default = null): array
    {
        if ($default === null) {
            $default = ['sqladmin', 'sqladmin', 'localhost', '3306', 'database'];
        } else {
            $default = $this->extract($default);
        }

        preg_match(self::DSN_PATTERN, $dsn, $matches, PREG_UNMATCHED_AS_NULL);
        array_shift($matches);

        return [
            $matches[0] ?? $default[0],
            $matches[1] ?? $default[1],
            $matches[2] ?? $default[2],
            $matches[3] ?? $default[3],
            $matches[4] ?? $default[4],
        ];
    }

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @return mixed
     */
    public function getPass()
    {
        return $this->pass;
    }

    /**
     * @return mixed
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * @return mixed
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * @return mixed
     */
    public function getBase()
    {
        return $this->base;
    }
}
