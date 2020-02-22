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

use GreenCape\JoomlaCLI\Driver\Version;
use League\Flysystem\FileNotFoundException;
use League\Flysystem\Filesystem;
use RuntimeException;

/**
 * The driver factory instantiates the proper driver for the addressed Joomla! version.
 *
 * @package     GreenCape\JoomlaCLI
 * @subpackage  Driver
 * @since       Class available since Release 0.1.0
 */
class Factory
{
	/**
	 * Create a version specific driver to Joomla
	 *
	 * @param Filesystem $filesystem The Joomla file system
	 *
	 * @return  JoomlaDriver
	 * @throws FileNotFoundException
	 */
	public function create(Filesystem $filesystem): JoomlaDriver
	{
		$parts = explode('.', $this->loadVersion($filesystem)->getShortVersion());
		while (!empty($parts))
		{
			$version   = implode('Dot', $parts);
			$classname = __NAMESPACE__ . '\\Joomla' . $version . 'Driver';
			if (class_exists($classname))
			{
				return new $classname($filesystem);
			}
			array_pop($parts);
		}
		throw new RuntimeException('No driver found');
	}

	/**
	 * Load the Joomla version
	 *
	 * @param Filesystem $filesystem
	 *
	 * @return  mixed
	 *
	 * @throws RuntimeException
	 * @throws FileNotFoundException
	 */
	private function loadVersion(Filesystem $filesystem)
	{
		return new Version($filesystem);
	}
}
