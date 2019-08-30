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
 * @since           File available since Release __DEPLOY_VERSION__
 */

namespace GreenCape\JoomlaCLI\Documentation\API;

use GreenCape\JoomlaCLI\Documentation\API\Strategy\APIGeneratorInterface;

/**
 * @package     GreenCape\JoomlaCLI
 * @subpackage  Command
 * @since       Class available since Release __DEPLOY_VERSION__
 */
class APIGenerator
{
	/**
	 * @var APIGeneratorInterface
	 */
	private $generator;

	/**
	 * APIGenerator constructor.
	 *
	 * @param string $generator
	 */
	public function __construct(string $generator)
	{
		$classname       = 'Strategy\\' . ucfirst($generator);
		$this->generator = new $classname;
	}

	/**
	 * Generate API documentation using the selected tool
	 *
	 * @param string $title   The title for the documentation
	 * @param string $source  The directory containing the source files
	 * @param string $umlPath The path to the UML diagrams
	 * @param string $target  The target directory for the documentation
	 */
	public function run(string $title, string $source, string $umlPath, string $target): void
	{
		$this->generator->generate($title, $source, $title);
		$this->generator->embedUml($umlPath);
	}
}
