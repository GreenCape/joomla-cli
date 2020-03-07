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
 * @author          Niels Braczek <nbraczek@bsds.de>
 * @copyright   (C) 2012-2019 GreenCape, Niels Braczek <nbraczek@bsds.de>
 * @license         http://opensource.org/licenses/MIT The MIT license (MIT)
 * @link            http://greencape.github.io
 * @since           File available since Release __DEPLOY_VERSION__
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
 * Class UmlCommand
 *
 * @since  Class available since Release __DEPLOY_VERSION__
 */
class UmlCommand extends Command
{
    /**
     * @var string
     */
    private $home;

    /**
     * Configure the options for the command
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
                'J',
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
            )
        ;
    }

    /**
     * Execute the command
     *
     * @param  InputInterface   $input   An InputInterface instance
     * @param  OutputInterface  $output  An OutputInterface instance
     */
    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $generator = new UMLGenerator($input->getOption('jar'));
        $generator->setLogger(new ConsoleLogger($output));

        $this->setupClassMap($generator, $input, $output);
        $this->setupPredefinedDiagrams($generator, $input, $output);
        $this->setupSkin($generator, $input, $output);
        $this->setupSvgCreation($generator, $input, $output);

        $source = $this->setupSource($input, $output);
        $target = $this->setupTarget($input, $output);
        $generator->generate($source, $target);
    }

    /**
     * @param  UMLGenerator     $generator
     * @param  InputInterface   $input
     * @param  OutputInterface  $output
     */
    private function setupClassMap(UMLGenerator $generator, InputInterface $input, OutputInterface $output): void
    {
        $classMapFile = $input->getOption('classmap');
        if (!empty($classMapFile)) {
            $output->writeln("Including class aliases from $classMapFile", OutputInterface::VERBOSITY_VERBOSE);
            $generator->classMap($classMapFile);
        }
    }

    /**
     * @param  UMLGenerator     $generator
     * @param  InputInterface   $input
     * @param  OutputInterface  $output
     */
    private function setupPredefinedDiagrams(
        UMLGenerator $generator,
        InputInterface $input,
        OutputInterface $output
    ): void {
        $predefined = $input->getOption('predefined');
        if (!empty($predefined)) {
            if ($predefined === 'php') {
                $predefined = $this->home . '/build/plantuml/php';
            }
            $output->writeln("Including predefined diagrams from $predefined", OutputInterface::VERBOSITY_VERBOSE);
            $generator->includeReferences($predefined);
        }
    }

    /**
     * @param  UMLGenerator     $generator
     * @param  InputInterface   $input
     * @param  OutputInterface  $output
     */
    private function setupSkin(UMLGenerator $generator, InputInterface $input, OutputInterface $output): void
    {
        $skin = $input->getOption('skin');
        if (preg_match('~^[\w-]+$~', $skin)) {
            $skin = $this->home . "/build/config/plantuml/skin-{$skin}.puml";
        }
        $output->writeln("Using skin $skin", OutputInterface::VERBOSITY_VERBOSE);
        $generator->skin($skin);
    }

    /**
     * @param  UMLGenerator     $generator
     * @param  InputInterface   $input
     * @param  OutputInterface  $output
     */
    private function setupSvgCreation(UMLGenerator $generator, InputInterface $input, OutputInterface $output): void
    {
        if ($input->getOption('no-svg')) {
            $output->writeln("Keeping UML source files, no SVGs will be created", OutputInterface::VERBOSITY_VERBOSE);
            $generator->createSvg(false);
        } else {
            $output->writeln(" SVGs will be created, discarding UML source files", OutputInterface::VERBOSITY_VERBOSE);
            $generator->createSvg();
        }
    }

    /**
     * @param  InputInterface   $input
     * @param  OutputInterface  $output
     *
     * @return Fileset
     */
    private function setupSource(InputInterface $input, OutputInterface $output): Fileset
    {
        $dir = $input->getOption('basepath');
        $output->writeln("Creating UML diagrams from $dir", OutputInterface::VERBOSITY_NORMAL);
        $source = new Fileset($dir);
        $source->include('**/*.php');

        return $source;
    }

    /**
     * @param  InputInterface   $input
     * @param  OutputInterface  $output
     *
     * @return bool|string|string[]|null
     */
    protected function setupTarget(InputInterface $input, OutputInterface $output)
    {
        $targetDir = $input->getOption('output');
        $output->writeln("Storing results in $targetDir", OutputInterface::VERBOSITY_VERBOSE);

        return $targetDir;
    }
}
