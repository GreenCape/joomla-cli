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
 * @package     GreenCape\JoomlaCLI
 * @subpackage  Driver
 * @author      Niels Braczek <nbraczek@bsds.de>
 * @copyright   (C) 2012-2014 GreenCape, Niels Braczek <nbraczek@bsds.de>
 * @license     http://opensource.org/licenses/GPL-2.0 GNU General Public License, version 2.0 (GPLv2)
 * @link        http://www.greencape.com/
 * @since       File available since Release 0.1.0
 */

namespace GreenCape\JoomlaCLI;

/**
 * The driver factory instantiates the proper driver for the addressed Joomla! version.
 *
 * @package     GreenCape\JoomlaCLI
 * @subpackage  Driver
 * @author      Niels Braczek <nbraczek@bsds.de>
 * @copyright   (C) 2012-2014 GreenCape, Niels Braczek <nbraczek@bsds.de>
 * @license     http://opensource.org/licenses/GPL-2.0 GNU General Public License, version 2.0 (GPLv2)
 * @link        http://www.greencape.com/
 * @since       File available since Release 1.0.0
 */
class DriverFactory
{
	/**
	 * Create a version specific driver to Joomla
	 *
	 * @param   string  $basePath  The Joomla base path (same as JPATH_BASE within Joomla)
	 *
	 * @return  JoomlaDriver
	 *
	 * @throws  \RuntimeException
	 */
	public function create($basePath)
	{
		$parts = explode('.', $this->loadVersion($basePath)->getShortVersion());
		while (!empty($parts))
		{
			$version = implode('_', $parts);
			$classname = __NAMESPACE__ . '\\Joomla' . $version . 'Driver';
			if (class_exists($classname))
			{
				return new $classname;
			}
			array_pop($parts);
		}
		throw new \RuntimeException('No driver found');
	}

	/**
	 * Load the Joomla version
	 *
	 * @param   string $basePath  The Joomla base path (same as JPATH_BASE within Joomla)
	 *
	 * @return  \JVersion
	 *
	 * @throws  \RuntimeException
	 */
	private function loadVersion($basePath)
	{
		static $locations = array(
			'/libraries/cms/version/version.php',
			'/libraries/joomla/version.php',
		);

		define('_JEXEC', 1);

		foreach ($locations as $location)
		{
			if (file_exists($basePath . $location))
			{
				$code = file_get_contents($basePath . $location);
				$code = str_replace("defined('JPATH_BASE')", "defined('_JEXEC')", $code);
				eval('?>' . $code);
				$classes = get_declared_classes();
				return new \JVersion;
			}
		}
		throw new \RuntimeException('Unable to locate version information');
	}
}
