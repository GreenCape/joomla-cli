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
 * @since           File available since Release 0.1.0
 */

namespace GreenCape\JoomlaCLI\Documentation\UML;

use GreenCape\JoomlaCLI\Fileset;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;

/**
 * @package     GreenCape\JoomlaCLI
 * @since       Class available since Release 0.3.0
 */
class UMLGenerator implements LoggerAwareInterface
{
    private $dir;
    private $skin;
    private $jar;
    private $includeRef = true;
    private $predefinedClasses;
    private $classMap;
    private $createSvg  = true;

    use LoggerAwareTrait;

    /**
     * UMLGenerator constructor.
     *
     * @param  string  $jar
     */
    public function __construct(string $jar)
    {
        $this->jar    = $jar;
        $this->logger = new NullLogger;
    }

    /**
     * @param  string  $repository  Path to collection of predefined class diagrams
     *
     * @return UMLGenerator
     */
    public function includeReferences(string $repository = null): self
    {
        $this->predefinedClasses = $repository;
        $this->includeRef        = true;

        return $this;
    }

    /**
     * @return UMLGenerator
     */
    public function excludeReferences(): self
    {
        $this->includeRef = false;

        return $this;
    }

    public function createSvg($create = true): void
    {
        $this->createSvg = $create;
    }

    /**
     * @param $skin
     *
     * @return UMLGenerator
     */
    public function skin($skin): self
    {
        $this->skin = $skin;

        return $this;
    }

    /**
     * @param  string  $classMapFile
     *
     * @return UMLGenerator
     */
    public function classMap(string $classMapFile): self
    {
        $this->classMap = $classMapFile;

        return $this;
    }

    /**
     * @param  Fileset  $source
     * @param  string   $targetDir
     */
    public function generate(Fileset $source, $targetDir): void
    {
        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        $classNameCollector = new ClassNameCollector;
        $classNameCollector->setLogger($this->logger);

        $annotationCollector = new AnnotationCollector;
        $annotationCollector->setLogger($this->logger);

        $scanner = new UMLScanner();
        $scanner->addCollector($classNameCollector);
        $scanner->addCollector($annotationCollector);

        $scanner->scan($source);

        if (!empty($this->classMap)) {
            $classNameCollector->addClassMap($this->classMap);
        }

        $flags = 0;

        if ($this->includeRef === false) {
            $flags |= UMLCollector::NO_INCLUDES;
        } elseif (!empty($this->predefinedClasses)) {
            $cmd = "cp -fu {$this->predefinedClasses}/*.puml {$targetDir}";
            $this->logger->debug("Copying predefined diagrams to temporary directory\n\$ {$cmd}");
            shell_exec($cmd);
        }

        $count = $scanner->writeDiagrams($targetDir, $flags);

        if (file_exists($this->skin)) {
            copy($this->skin, $targetDir . '/skin.puml');
        }

        if ($this->createSvg) {
            $relevantFiles = $scanner->getRelevantFiles();

            $files = implode("' '", $relevantFiles);
            $dir   = getcwd();
            $cmd   = "cd {$targetDir} && java -jar '{$this->jar}' -tsvg -progress '{$files}' && cd {$dir}";
            $this->logger->debug("Generating SVG files\n\$ {$cmd}");
            shell_exec($cmd);
            echo "\n";

            $count = count($relevantFiles);

            $cmd = "rm {$targetDir}/*.puml";
            $this->logger->debug("Removing no longer needed diagram sources\n\$ {$cmd}");
            shell_exec($cmd);
        }

        $this->logger->info("Created $count UML diagrams.");
    }
}
