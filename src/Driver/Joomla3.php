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

namespace GreenCape\JoomlaCLI\Driver;

use GreenCape\JoomlaCLI\Driver\Crypt\CryptInterface;
use GreenCape\JoomlaCLI\Driver\Crypt\PasswordHash;
use GreenCape\JoomlaCLI\Utility\Expander;
use JFactory;
use League\Flysystem\Filesystem;

/**
 * Version specific methods
 *
 * @since  Class available since Release 0.1.0
 */
class Joomla3Driver extends JoomlaDriver
{
    /**
     * Joomla3Driver constructor.
     *
     * @param  Filesystem  $filesystem
     * @param  string      $basePath
     */
    public function __construct(Filesystem $filesystem, string $basePath)
    {
        parent::__construct($filesystem, $basePath);

        if (!defined('_JEXEC')) {
            define('_JEXEC', 1);
        }
    }

    /**
     * Set a configuration value.
     *
     * @param  string  $key    The key
     * @param  mixed   $value  The value
     *
     * @return  mixed  The value
     */
    public function setCfg($key, $value)
    {
        return JFactory::getConfig()->set($key, $value);
    }

    /**
     * Gets a configuration value.
     *
     * @param  string  $key  The name of the value to get
     *
     * @return  mixed  The value
     */
    public function getCfg($key)
    {
        return JFactory::getConfig()->get($key);
    }

    /**
     * Get the queries for creating a super user account
     *
     * @param  string  $engine
     * @param  string  $adminUser
     * @param  string  $adminPassword
     * @param  string  $adminEmail
     *
     * @return string SQL statements
     */
    public function getRootAccountCreationQuery(string $engine, $adminUser, $adminPassword, $adminEmail): string
    {
        $templateDir = $this->buildTemplates . '/' . $engine;
        $crypt       = $this->crypt();

        /** @todo Escape admin* values */
        $values = [
            'adminUser'   => $adminUser,
            'cryptPass'   => $crypt->encryptPassword($adminPassword, $crypt->createSalt()),
            'adminEmail'  => $adminEmail,
            'installDate' => date('Y-m-d H:i:s'),
        ];

        return (new Expander())->expand(file_get_contents($templateDir . '/admin-3.sql'), $values);
    }

    /**
     * Get the encryption strategy
     *
     * @return CryptInterface
     */
    public function crypt(): CryptInterface
    {
        return new PasswordHash();
    }
}
