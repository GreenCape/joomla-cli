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

use Exception;
use JFactory;
use JText;
use League\Flysystem\FileNotFoundException;
use League\Flysystem\Filesystem;

/**
 * The abstract version driver provides common methods for most Joomla! versions.
 *
 * @package     GreenCape\JoomlaCLI
 * @subpackage  Driver
 * @since       Class available since Release 0.1.0
 */
abstract class JoomlaDriver
{
    /**
     * @var string
     */
    protected $basePath;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * JoomlaDriver constructor.
     *
     * @param  Filesystem  $filesystem
     */
    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
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
        if ($application !== 'site') {
            $this->basePath .= '/' . $application;
        }

        $server  = [
            'HTTP_HOST'       => 'undefined',
            'HTTP_USER_AGENT' => 'undefined',
            'REQUEST_METHOD'  => 'GET',
        ];
        $_SERVER = array_merge($_SERVER, $server);

        if (file_exists($this->basePath . '/defines.php')) {
            include_once $this->basePath . '/defines.php';
        }

        if (!defined('_JDEFINES')) {
            define('JPATH_BASE', $this->basePath);
            require_once JPATH_BASE . '/includes/defines.php';
        }

        require_once JPATH_BASE . '/includes/framework.php';

        if ($application === 'administrator') {
            require_once JPATH_BASE . '/includes/helper.php';
            require_once JPATH_BASE . '/includes/toolbar.php';

            // JUri uses $_SERVER['HTTP_HOST'] without check
            $_SERVER['HTTP_HOST'] = 'CLI';
        }

        $app = JFactory::getApplication($application);
        $app->initialise();
    }

    /**
     * Set a configuration value.
     *
     * @param  string  $key    The key
     * @param  mixed   $value  The value
     *
     * @return  mixed  The value
     */
    abstract public function setCfg($key, $value);

    /**
     * Gets a configuration value.
     *
     * @param  string  $key  The name of the value to get
     *
     * @return  mixed  The value
     */
    abstract public function getCfg($key);

    /**
     * @param $manifest
     *
     * @return array
     */
    public function getExtensionInfo($manifest): array
    {
        $data                = [];
        $data['type']        = (string)$manifest['type'];
        $data['extension']   = (string)$manifest->name;
        $data['name']        = JText::_($manifest->name);
        $data['version']     = (string)$manifest->version;
        $data['description'] = JText::_($manifest->description);

        return $data;
    }

    /**
     * @return Version
     * @throws FileNotFoundException
     */
    public function getVersion(): Version
    {
        return new Version($this->filesystem);
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
    abstract public function getRootAccountCreationQuery($adminUser, $adminPassword, $adminEmail): array;
}
