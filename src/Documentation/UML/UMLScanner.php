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

namespace GreenCape\JoomlaCLI\Documentation\UML;

use GreenCape\JoomlaCLI\Fileset;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\Parser;
use PhpParser\ParserFactory;

/**
 * Class UMLScanner
 *
 * @since  Class available since Release __DEPLOY_VERSION__
 */
class UMLScanner
{
    /**
     * @var Parser
     */
    private $parser;
    /**
     * @var NodeTraverser
     */
    private $traverser;
    /**
     * @var UMLCollectorInterface[]
     */
    private $collectors;
    /**
     * @var array
     */
    private $relevantFiles;

    public function __construct()
    {
        $this->parser    = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);
        $this->traverser = new NodeTraverser();
        $this->traverser->addVisitor(new NameResolver);
    }

    public function addCollector(UMLCollectorInterface $collector): void
    {
        $this->collectors[] = $collector;
        $this->traverser->addVisitor($collector);
    }

    public function scan(Fileset $source): void
    {
        foreach ($source->getFiles() as $file) {
            $this->traverser->traverse($this->parser->parse(file_get_contents($file)));
        }
    }

    public function writeDiagrams($targetDir, $flags = 0): int
    {
        $count               = 0;
        $this->relevantFiles = [];

        foreach ($this->collectors as $collector) {
            $count += $collector->writeDiagrams($targetDir, $flags);
            /** @noinspection SlowArrayOperationsInLoopInspection */
            $this->relevantFiles = array_merge($this->relevantFiles, $collector->getRelevantFiles());
        }

        return $count;
    }

    /**
     * Gets a list of relevant (generated and included) files
     *
     * @return array
     */
    public function getRelevantFiles(): array
    {
        return array_unique($this->relevantFiles);
    }
}
