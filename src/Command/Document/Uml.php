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
 * @since           File available since Release 0.2.0
 */

namespace GreenCape\JoomlaCLI\Command\Document;

use GreenCape\JoomlaCLI\Command;
use GreenCape\JoomlaCLI\Documentation\UML\UMLGenerator;
use GreenCape\JoomlaCLI\Fileset;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @package     GreenCape\JoomlaCLI
 * @subpackage  Command
 * @since       Class available since Release 0.2.0
 */
class UmlCommand extends Command
{
	/**
	 * @var string
	 */
	private $home;

	/**
	 * Configure the options for the install command
	 *
	 * @return  void
	 */
	protected function configure(): void
	{
		$this->home = dirname(__DIR__, 3);

		$this
			->setName('document:uml')
			->setDescription('Generates UML diagrams')
			->addOption(
				'jar',
				'j',
				InputOption::VALUE_REQUIRED,
				"Path to the PlantUML jar file",
				$this->home . '/build/plantuml/plantuml.jar'
			)
			->addOption(
				'classmap',
				'c',
				InputOption::VALUE_OPTIONAL,
				"Path to the Joomla! classmap file",
				'joomla/libraries/classmap.php'
			)
			->addOption(
				'predefined',
				'p',
				InputOption::VALUE_OPTIONAL,
				"Path to predefined diagrams",
				'build/uml'
			)
			->addOption(
				'skin',
				's',
				InputOption::VALUE_REQUIRED,
				"Name ('bw', 'bw-gradient' or 'default') of or path to the skin",
				'default'
			)
			->addOption(
				'output',
				'o',
				InputOption::VALUE_REQUIRED,
				"Output directory",
				'build/report/uml'
			)
			->addOption(
				'no-svg',
				null,
				InputOption::VALUE_NONE,
				"Do not create .svg files, keep .puml files instead"
			);
	}

	/**
	 * Execute the command
	 *
	 * @param InputInterface  $input  An InputInterface instance
	 * @param OutputInterface $output An OutputInterface instance
	 */
	protected function execute(InputInterface $input, OutputInterface $output): void
	{
		$generator = new UMLGenerator($input->getOption('jar'));
		$generator->setLogger(new ConsoleLogger($output));

		$source = new Fileset($input->getOption('basepath'));
		$source->include('**/*.php');

		$classMapFile = $input->getOption('classmap');
		if (!empty($classMapFile))
		{
			$generator->classMap($classMapFile);
		}

		$predefined = $input->getOption('predefined');
		if (!empty($predefined))
		{
			if ($predefined === 'php')
			{
				$predefined = $this->home . '/build/plantuml/php';
			}
			$generator->includeReferences($predefined);
		}

		$skin = $input->getOption('skin');
		if (preg_match('~^[\w-]+$~', $skin))
		{
			$skin = $this->home . "/build/config/plantuml/skin-{$skin}.puml";
		}
		$generator->skin($skin);

		$generator->generate($source, $input->getOption('output'));
	}
}
