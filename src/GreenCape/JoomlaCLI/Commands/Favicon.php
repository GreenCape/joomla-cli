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
 * @subpackage  Command
 * @author      Niels Braczek <nbraczek@bsds.de>
 * @copyright   (C) 2012-2015 GreenCape, Niels Braczek <nbraczek@bsds.de>
 * @license     http://opensource.org/licenses/MIT The MIT license (MIT)
 * @link        http://greencape.github.io
 * @since       File available since Release 0.1.1
 */

namespace GreenCape\JoomlaCLI;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * The favicons command creates a set of favicons, apple-touch-icons, and mstiles from a single image.
 *
 * @package     GreenCape\JoomlaCLI
 * @subpackage  Command
 * @see         http://realfavicongenerator.net/faq
 */
class FaviconsCommand extends Command
{
	/**
	 * Configure the options for the favicons command
	 *
	 * @return  void
	 */
	protected function configure()
	{
		$this
			->setName('favicons')
			->setDescription('Create favicons')
			->addOption(
				'legacy',
				'l',
				InputOption::VALUE_NONE,
				'Create outdated formats (e.g., iOS 6).'
			)
			->addArgument(
				'image',
				InputArgument::REQUIRED,
				'The path to the image.'
			);
	}

	/**
	 * Execute the favicons command
	 *
	 * @param   InputInterface  $input  An InputInterface instance
	 * @param   OutputInterface $output An OutputInterface instance
	 *
	 * @return  void
	 */
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$this->setupEnvironment('site', $input, $output);
	}

	protected function createFavicons($sourceImage, $legacy)
	{
		$html = '';

		return $html;
	}

	protected function createAppleTouchIcons($sourceImage, $legacy)
	{
		$html = '';

		return $html;
	}

	protected function createMstiles($sourceImage, $legacy)
	{
		$html = '';

		return $html;
	}

	private function render($svg, $width, $height, $filename)
	{
		if (!class_exists('\Imagick'))
		{
			throw new \RuntimeException('ImageMagick is needed to support SVG.');
		}

		// Manipulate the SVG, if necessary

		$im = new \Imagick();

		$im->setBackgroundColor(new \ImagickPixel('transparent'));
		$im->readImageBlob($svg);

		$im->setImageFormat('png24');
		$im->resizeImage($width, $height, \Imagick::FILTER_LANCZOS, 1);

		$im->writeImage($filename);
		$im->clear();
		$im->destroy();
	}
}
