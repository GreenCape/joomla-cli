<?php
/**
 * GreenCape Joomla Command Line Interface
 *
 * Copyright (c) 2012-2013, Niels Braczek <nbraczek@bsds.de>.
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
 *   * Neither the name of Niels Braczek nor the names of his
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
 * @package     GreenCape\JoomlaCLI
 * @subpackage  Driver
 * @author      Niels Braczek <nbraczek@bsds.de>
 * @copyright   2012-2013 Niels Braczek <nbraczek@bsds.de>
 * @license     http://opensource.org/licenses/AGPL-3.0 GNU AFFERO GENERAL PUBLIC LICENSE, Version 3 (AGPL-3.0)
 * @link        http://www.bsds.de/
 * @since       File available since Release 1.0.0
 */

namespace GreenCape\JoomlaCLI;

/**
 * The abstract version driver provides common methods for most Joomla! versions.
 *
 * @package     GreenCape\JoomlaCLI
 * @subpackage  Driver
 * @author      Niels Braczek <nbraczek@bsds.de>
 * @copyright   2012-2013 Niels Braczek <nbraczek@bsds.de>
 * @license     http://opensource.org/licenses/AGPL-3.0 GNU AFFERO GENERAL PUBLIC LICENSE, Version 3 (AGPL-3.0)
 * @link        http://www.bsds.de/
 * @since       File available since Release 1.0.0
 */
abstract class JoomlaDriver
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

		if (file_exists($basePath . '/defines.php'))
		{
			include_once $basePath . '/defines.php';
		}

		if (!defined('_JDEFINES'))
		{
			define('JPATH_BASE', $basePath);
			require_once JPATH_BASE . '/includes/defines.php';
		}

		require_once JPATH_BASE . '/includes/framework.php';

		if ($application == 'administrator')
		{
			require_once JPATH_BASE.'/includes/helper.php';
			require_once JPATH_BASE.'/includes/toolbar.php';

			// JUri uses $_SERVER['HTTP_HOST'] without check
			$_SERVER['HTTP_HOST'] = 'CLI';
		}

		$app = \JFactory::getApplication($application);
		$app->initialise();
	}

	/**
	 * Set a configuration value.
	 *
	 * @param   string  $key    The key
	 * @param   mixed   $value  The value
	 *
	 * @return  mixed  The value
	 */
	abstract public function setCfg($key, $value);

	/**
	 * Gets a configuration value.
	 *
	 * @param   string  $key  The name of the value to get
	 *
	 * @return  mixed  The value
	 */
	abstract public function getCfg($key);

	/**
	 * @param $manifest
	 *
	 * @return array
	 */
	abstract public function getExtensionInfo($manifest);
}
