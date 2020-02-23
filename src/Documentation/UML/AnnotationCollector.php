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

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\NodeVisitorAbstract;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

/**
 * Class AnnotationCollector
 *
 * @since  Class available since Release __DEPLOY_VERSION__
 */
class AnnotationCollector extends NodeVisitorAbstract implements UMLCollector, LoggerAwareInterface
{
    private $currentClass;

    private $uml = [];
    /**
     * @var array
     */
    private $relevantFiles = [];

    use LoggerAwareTrait;

    public function enterNode(Node $node)
    {
        if ($node instanceof ClassLike) {
            if ($this->isAnonymous($node)) {
                return;
            }

            $this->currentClass = (string)$node->namespacedName;
        } elseif ($node instanceof ClassMethod) {
            $this->createUml((string)$node->name, (string)$node->getDocComment());
        }
    }

    public function leaveNode(Node $node)
    {
        if ($node instanceof ClassLike) {
            $this->currentClass = null;
        }
    }

    /**
     * @param  ClassLike  $node
     *
     * @return bool
     */
    private function isAnonymous(ClassLike $node): bool
    {
        return !isset($node->namespacedName);
    }

    /**
     * @param  string  $name
     * @param  string  $comment
     */
    private function createUml(string $name, string $comment): void
    {
        if (preg_match_all('~@startuml(.*?)@enduml~sm', $comment, $matches, PREG_SET_ORDER) === 0) {
            return;
        }

        $uml = "@startuml\n!include skin.puml\n!startsub INNER\n";

        foreach ($matches as $match) {
            $uml .= preg_replace("~\n\s*\*\s*~", "\n", $match[1]);
        }

        $uml .= "!endsub\n@enduml\n";

        $this->add($this->currentClass . '::' . $name, $uml);
    }

    public function add($name, $uml, $includes = []): void
    {
        $this->uml[strtolower($name)] = $uml;
    }

    public function writeDiagrams($targetDir, $flags): int
    {
        foreach ($this->uml as $name => $uml) {
            $filename              = $this->filename($name);
            $this->relevantFiles[] = $filename;

            file_put_contents($targetDir . '/' . $filename, $uml);
        }

        return count($this->uml);
    }

    /**
     * Gets a list of relevant (generated and included) files
     *
     * @return array
     */
    public function getRelevantFiles(): array
    {
        return $this->relevantFiles;
    }

    private function filename(string $name): string
    {
        [$class, $method] = explode('::', $name);

        return strtolower('annotation-' . trim(preg_replace('~\W+~', '.', $class), '.') . '-' . $method . '.puml');
    }
}
