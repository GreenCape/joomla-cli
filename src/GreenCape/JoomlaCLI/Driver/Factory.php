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

namespace GreenCape\JoomlaCLI;

use JVersion;
use RuntimeException;

/**
 * The driver factory instantiates the proper driver for the addressed Joomla! version.
 *
 * @package     GreenCape\JoomlaCLI
 * @subpackage  Driver
 * @since       Class available since Release 0.1.0
 */
class DriverFactory
{
	/**
	 * Create a version specific driver to Joomla
	 *
	 * @param string $basePath The Joomla base path (same as JPATH_BASE within Joomla)
	 *
	 * @return  JoomlaDriver
	 *
	 * @throws  RuntimeException
	 */
	public function create($basePath): JoomlaDriver
	{
		$parts = explode('.', $this->loadVersion($basePath)->getShortVersion());
		while (!empty($parts))
		{
			$version   = implode('Dot', $parts);
			$classname = __NAMESPACE__ . '\\Joomla' . $version . 'Driver';
			if (class_exists($classname))
			{
				return new $classname;
			}
			array_pop($parts);
		}
		throw new RuntimeException('No driver found');
	}

	/**
	 * Load the Joomla version
	 *
	 * @param string $basePath The Joomla base path (same as JPATH_BASE within Joomla)
	 *
	 * @return  JVersion
	 *
	 * @throws  RuntimeException
	 */
	private function loadVersion($basePath): JVersion
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

				return new JVersion;
			}
		}
		throw new RuntimeException('Unable to locate version information');
	}
}
