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

use JLoader;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Trait_;
use PhpParser\NodeVisitorAbstract;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;

/**
 * Class ClassNameCollector
 *
 * The ClassNameCollector collects interfaces, classes and traits.
 *
 * @since  Class available since Release __DEPLOY_VERSION__
 */
class ClassNameCollector extends NodeVisitorAbstract implements UMLCollectorInterface, LoggerAwareInterface
{
    private $classes = [];
    private $uml     = [];
    private $currentClass;

    /**
     * @var string
     */
    private $separator = '\\\\';

    /**
     * @var array
     */
    private $relevantFiles;

    use LoggerAwareTrait;

    public function __construct()
    {
        $this->setLogger(new NullLogger);
    }

    public function enterNode(Node $node)
    {
        if ($node instanceof Class_) {
            if ($this->isAnonymous($node)) {
                return;
            }

            $this->addClass($node, 'class');
            $this->currentClass = (string)$node->namespacedName;
        } elseif ($node instanceof Interface_) {
            $this->addClass($node, 'interface');
        } elseif ($node instanceof Trait_) {
            $this->addClass($node, 'trait');
        } elseif ($node instanceof Node\Stmt\TraitUse) {
            $this->classes[$this->currentClass]['traits'] += array_reduce(
                $node->traits,
                static function ($carry, $node) {
                    $carry[] = implode('\\', $node->parts);

                    return $carry;
                }
            );
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
     * @param  ClassLike  $node
     * @param  string     $type
     */
    private function addClass(ClassLike $node, string $type): void
    {
        $name                 = (string)$node->namespacedName;
        $this->classes[$name] = [
            'name'       => $name,
            'type'       => $type,
            'flags'      => (int)($node->flags ?? 0),
            'extends'    => $this->namespaced($node->extends ?? []),
            'implements' => $this->namespaced($node->implements ?? []),
            'traits'     => [],
        ];
        $this->logger->debug("Found {$type} {$name}");
    }

    private function namespaced($node)
    {
        if ($node instanceof Node\Name\FullyQualified) {
            return implode('\\', $node->parts);
        }

        return array_reduce(
            $node,
            function (array $carry, Node $node) {
                $carry[] = $this->namespaced($node);

                return $carry;
            },
            []
        );
    }

    public function add($name, $uml, $includes = []): void
    {
        $this->uml[strtolower($name)] = [
            'diagram' => $uml,
            'include' => $includes,
        ];
        $this->logger->debug("Added UML for $name");
    }

    public function writeDiagrams($targetDir, $flags = 0): int
    {
        $this->createClassUml($this->classes);

        $count = 0;

        foreach (array_keys($this->uml) as $className) {
            file_put_contents($targetDir . '/' . $this->filename($className), $this->render($className, $flags));
            $count++;
            $this->logger->debug("Wrote {$this->filename($className)}");
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
        return $this->relevantFiles;
    }

    private function createClassUml(array $classes): void
    {
        foreach ($classes as $class => $info) {
            $class    = $this->exchangeSeparator($info['name'], $this->separator);
            $static   = ($info['flags'] & Class_::MODIFIER_STATIC) !== 0 ? 'static ' : '';
            $abstract = ($info['flags'] & Class_::MODIFIER_ABSTRACT) !== 0 ? 'abstract ' : '';
            $final    = ($info['flags'] & Class_::MODIFIER_FINAL) !== 0 ? ' << final >>' : '';
            $type     = $info['type'];

            if ($type === 'trait') {
                $type  = 'class';
                $final = empty($final) ? ' << (T, trait) >>' : ' << (T, trait) final >>';
            }

            $uml      = "{$abstract}{$static}{$type} {$class}{$final}\n";
            $includes = [];

            foreach ((array)($info['extends'] ?? []) as $parent) {
                $parent     = $this->exchangeSeparator($parent, $this->separator);
                $uml        .= "{$parent} <|-- {$class}\n";
                $includes[] = $parent;
            }

            foreach ($info['implements'] ?? [] as $interface) {
                $interface  = $this->exchangeSeparator($interface, $this->separator);
                $uml        .= "interface {$interface}\n";
                $uml        .= "{$interface} <|.. {$class}\n";
                $includes[] = $interface;
            }

            foreach ($info['traits'] ?? [] as $trait) {
                $trait      = $this->exchangeSeparator($trait, $this->separator);
                $uml        .= "class {$trait}  << (T, orchid) >>\n";
                $uml        .= "{$trait} - {$class}\n";
                $includes[] = $trait;
            }

            $this->logger->debug("Created UML for {$class}");
            $this->relevantFiles[] = $this->filename($class);

            $this->add($class, $uml, $includes);
        }
    }

    private function filename(string $className): string
    {
        $className = trim(preg_replace('~\W+~', '.', $className), '.');

        return strtolower('class-' . $className . '.puml');
    }

    private function render($namespace, $flags = 0): string
    {
        $separator = '\\\\';
        $uml       = "@startuml\n!include skin.puml\nset namespaceSeparator \\\\\nhide members\nhide << alias >> circle\n!startsub INNER\n";

        $namespace = trim(str_replace('\\', $separator, $namespace), '\\');
        $rendered  = [];
        $includes  = [];

        $withIncludes  = ($flags & self::NO_INCLUDES) === 0;
        $namespaceFlag = ($flags & self::NAMESPACE) === self::NAMESPACE;
        $greedyFlag    = ($flags & self::GREEDY) === self::GREEDY;

        /** @noinspection NestedTernaryOperatorInspection */
        $stopper = $namespaceFlag ? ($greedyFlag ? '.*' : '\b') : '$';

        foreach ($this->uml as $className => $data) {
            if (!preg_match("~^{$namespace}{$stopper}~i", trim($className, '\\'))) {
                continue;
            }

            $rendered[$className] = true;
            $uml                  .= $data['diagram'];

            foreach ($data['include'] as $include) {
                $includes[$include] = true;
            }
        }

        if ($withIncludes) {
            foreach (array_keys($rendered) as $alreadyHandled) {
                $includes[$alreadyHandled] = false;
            }

            foreach ($includes as $include => $doInclude) {
                if ($doInclude) {
                    $uml .= '!includesub ' . $this->filename($include) . "!INNER\n";
                }
            }
        }

        $uml .= "!endsub\n@enduml\n";

        return $uml;
    }

    /**
     * @param  string  $class
     * @param  string  $separator
     *
     * @return string
     */
    private function exchangeSeparator(string $class, string $separator): string
    {
        return str_replace('\\', $separator, $class);
    }

    public function addClassMap(string $classMapFile): void
    {
        JLoader::$separator = $this->separator;
        JLoader::$collector = $this;
        JLoader::$logger    = $this->logger;

        define('_JEXEC', 1);
        include $classMapFile;
    }
}
