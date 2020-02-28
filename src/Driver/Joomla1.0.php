<?php /** @noinspection PhpUndefinedMethodInspection */

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

use Exception;
use GreenCape\JoomlaCLI\Driver\Crypt\CryptInterface;
use GreenCape\JoomlaCLI\Driver\Crypt\Salted16MD5;
use JText;
use League\Flysystem\Filesystem;

/**
 * Version specific methods
 *
 * @since  Class available since Release 0.1.0
 */
class Joomla1Dot0Driver extends JoomlaDriver
{
    /**
     * Joomla1Dot0Driver constructor.
     *
     * @param  Filesystem  $filesystem
     * @param  string      $basePath
     */
    public function __construct(Filesystem $filesystem, string $basePath)
    {
        parent::__construct($filesystem, $basePath);
        define('_VALID_MOS', 1);
    }

    /**
     * Setup the environment
     *
     * @param  string  $application  The application, eg., 'site' or 'administration'
     *
     * @return  void
     * @throws Exception
     */
    public function setupEnvironment($application = 'site'): void
    {
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
        return $GLOBALS['mosConfig_' . $key] = $value;
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
        return $GLOBALS['mosConfig_' . $key];
    }

    /**
     *
     * @param  object  $manifest
     *
     * @return array
     */
    public function getExtensionInfo($manifest): array
    {
        $data                = [];
        $manifest            = $manifest->document;
        $data['type']        = (string)$manifest->attributes('type');
        $data['extension']   = (string)$manifest->name[0]->data();
        $data['name']        = JText::_($manifest->name[0]->data());
        $data['version']     = (string)$manifest->version[0]->data();
        $data['description'] = JText::_($manifest->description[0]->data());

        return $data;
    }

    /**
     * Get the queries for creating a super user account
     *
     * @param  string  $adminUser
     * @param  string  $adminPassword
     * @param  string  $adminEmail
     *
     * @return array SQL statements
     */
    public function getRootAccountCreationQuery($adminUser, $adminPassword, $adminEmail): array
    {
        $crypt = $this->crypt();

        $salt      = $crypt->createSalt();
        $cryptPass = $crypt->encryptPassword($adminPassword, $salt);

        $nullDate    = '0000-00-00 00:00:00';
        $installDate = date('Y-m-d H:i:s');

        /** @todo Escape admin* values */
        return [
            "INSERT INTO `#__users` VALUES (62, 'Administrator', '$adminUser', '$adminEmail', '$cryptPass', 'Super Administrator', 0, 1, 25, '$installDate', '$nullDate', '', '')",
            "INSERT INTO `#__core_acl_aro` VALUES (10,'users','62',0,'Administrator',0)",
            "INSERT INTO `#__core_acl_groups_aro_map` VALUES (25,'',10)",
        ];
    }

    /**
     * Get the encryption strategy
     *
     * @return CryptInterface
     */
    public function crypt(): CryptInterface
    {
        return new Salted16MD5();
    }
}
