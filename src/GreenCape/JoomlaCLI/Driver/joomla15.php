<?php
/**
 * GreenCape Joomla Command Line Interface
 *
 * Copyright (c) 2012-2014, Niels Braczek <nbraczek@bsds.de>.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *   * Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 *
 *   * Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in
 *     the documentation and/or other materials provided with the
 *     distribution.
 *
 *   * Neither the name of GreenCape or Niels Braczek nor the names of his
 *     contributors may be used to endorse or promote products derived
 *     from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @package         GreenCape\JoomlaCLI
 * @subpackage      Driver
 * @author          Niels Braczek <nbraczek@bsds.de>
 * @copyright   (C) 2012-2014 GreenCape, Niels Braczek <nbraczek@bsds.de>
 * @license         http://opensource.org/licenses/GPL-2.0 GNU General Public License, version 2.0 (GPLv2)
 * @link            http://www.greencape.com/
 * @since           File available since Release 0.1.0
 */

namespace GreenCape\JoomlaCLI;

/**
 * Version specific methods
 *
 * @package         GreenCape\JoomlaCLI
 * @subpackage      Driver
 * @author          Niels Braczek <nbraczek@bsds.de>
 * @copyright   (C) 2012-2014 GreenCape, Niels Braczek <nbraczek@bsds.de>
 * @license         http://opensource.org/licenses/GPL-2.0 GNU General Public License, version 2.0 (GPLv2)
 * @link            http://www.greencape.com/
 * @since           File available since Release 1.0.0
 */
class Joomla15Driver extends JoomlaDriver
{
    /**
     * Setup the environment
     *
     * @param   string $basePath The root of the Joomla! application
     * @param   string $application The application, eg., 'site' or 'administration'
     *
     * @return  void
     */
    public function setupEnvironment($basePath, $application = 'site')
    {
        if ($application != 'site') {
            $basePath .= '/' . $application;
        }

        $server = array(
            'HTTP_HOST' => 'undefined',
            'HTTP_USER_AGENT' => 'undefined',
            'REQUEST_METHOD' => 'GET',
        );
        $_SERVER = array_merge($_SERVER, $server);

        define('JPATH_BASE', $basePath);
        define('DS', DIRECTORY_SEPARATOR);

        require_once JPATH_BASE . '/includes/defines.php';
        require_once JPATH_LIBRARIES . '/loader.php';

        spl_autoload_register('__autoload');

        require_once JPATH_BASE . '/includes/framework.php';

        if ($application == 'administrator') {
            require_once JPATH_BASE . '/includes/helper.php';
            require_once JPATH_BASE . '/includes/toolbar.php';
        }

        jimport('joomla.installer.installer');
        jimport('joomla.installer.helper');

        $mainframe = \JFactory::getApplication($application);
        $mainframe->initialise();
    }

    /**
     * Set a configuration value.
     *
     * @param   string $key The key
     * @param   mixed $value The value
     *
     * @return  mixed  The value
     */
    public function setCfg($key, $value)
    {
        return \JFactory::getConfig()
            ->setValue('config.' . $key, $value);
    }

    /**
     * Gets a configuration value.
     *
     * @param   string $key The name of the value to get
     *
     * @return  mixed  The value
     */
    public function getCfg($key)
    {
        return \JFactory::getConfig()
            ->getValue('config.' . $key);
    }

    /**
     * @param $manifest
     *
     * @return array
     */
    public function getExtensionInfo($manifest)
    {
        $data = array();
        $manifest = $manifest->document;
        $data['type'] = (string)$manifest->attributes('type');
        $data['extension'] = (string)$manifest->name[0]->data();
        $data['name'] = \JText::_($manifest->name[0]->data());
        $data['version'] = (string)$manifest->version[0]->data();
        $data['description'] = \JText::_($manifest->description[0]->data());

        return $data;
    }
}
