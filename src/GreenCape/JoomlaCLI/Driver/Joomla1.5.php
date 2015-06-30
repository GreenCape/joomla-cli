<?php
/**
 * GreenCape Joomla Command Line Interface
 *
 * MIT License
 *
 * Copyright (c) 2012-2015, Niels Braczek <nbraczek@bsds.de>. All rights reserved.
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
 * @package     GreenCape\JoomlaCLI
 * @subpackage  Driver
 * @author      Niels Braczek <nbraczek@bsds.de>
 * @copyright   (C) 2012-2015 GreenCape, Niels Braczek <nbraczek@bsds.de>
 * @license     http://opensource.org/licenses/MIT The MIT license (MIT)
 * @link        http://greencape.github.io
 * @since       File available since Release 0.1.0
 */

namespace GreenCape\JoomlaCLI;

/**
 * Version specific methods
 *
 * @package     GreenCape\JoomlaCLI
 * @subpackage  Driver
 * @since       Class available since Release 1.0.0
 */
class Joomla1_5Driver extends JoomlaDriver
{
	/**
	 * Setup the environment
	 *
	 * @param   string  $basePath     The root of the Joomla! application
	 * @param   string  $application  The application, eg., 'site' or 'administration'
	 *
	 * @return  void
	 */
	public function setupEnvironment($basePath, $application = 'site')
	{
		if ($application != 'site')
		{
			$basePath .= '/' . $application;
		}

		$server = array(
			'HTTP_HOST'       => 'undefined',
			'HTTP_USER_AGENT' => 'undefined',
			'REQUEST_METHOD'  => 'GET',
		);
		$_SERVER = array_merge($_SERVER, $server);

		define('JPATH_BASE', $basePath);
		define('DS', DIRECTORY_SEPARATOR);

		require_once JPATH_BASE . '/includes/defines.php';
		require_once JPATH_LIBRARIES . '/loader.php';

		spl_autoload_register('__autoload');

		require_once JPATH_BASE . '/includes/framework.php';

		if ($application == 'administrator')
		{
			require_once JPATH_BASE.'/includes/helper.php';
			require_once JPATH_BASE.'/includes/toolbar.php';

			// JUri uses $_SERVER['HTTP_HOST'] without check
			$_SERVER['HTTP_HOST'] = 'CLI';
		}

		jimport('joomla.installer.installer');
		jimport('joomla.installer.helper');

		$mainframe = \JFactory::getApplication($application);
		$mainframe->initialise();
	}

	/**
	 * Set a configuration value.
	 *
	 * @param   string  $key    The key
	 * @param   mixed   $value  The value
	 *
	 * @return  mixed  The value
	 */
	public function setCfg($key, $value)
	{
		return \JFactory::getConfig()->setValue('config.' . $key, $value);
	}

	/**
	 * Gets a configuration value.
	 *
	 * @param   string  $key  The name of the value to get
	 *
	 * @return  mixed  The value
	 */
	public function getCfg($key)
	{
		return \JFactory::getConfig()->getValue('config.' . $key);
	}

	/**
	 * @param $manifest
	 *
	 * @return array
	 */
	public function getExtensionInfo($manifest)
	{
		$data                = array();
		$manifest            = $manifest->document;
		$data['type']        = (string) $manifest->attributes('type');
		$data['extension']   = (string) $manifest->name[0]->data();
		$data['name']        = \JText::_($manifest->name[0]->data());
		$data['version']     = (string) $manifest->version[0]->data();
		$data['description'] = \JText::_($manifest->description[0]->data());

		return $data;
	}
}
