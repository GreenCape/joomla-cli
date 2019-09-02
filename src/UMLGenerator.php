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

namespace GreenCape\JoomlaCLI;

/**
 * @package     GreenCape\JoomlaCLI
 * @since       Class available since Release 0.2.0
 */
class UMLGenerator
{
	private $dir;
	private $skin;
	private $jar;
	private $includeRef = true;
	private $predefinedClasses;

	/**
	 * UMLGenerator constructor.
	 *
	 * @param string $jar
	 */
	public function __construct(string $jar)
	{
		$this->jar = $jar;
	}

	/**
	 * @param string $repository Path to collection of predefined class diagrams
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
	 * @param string[] $sourceFiles
	 * @param string   $targetDir
	 */
	public function generate($sourceFiles, $targetDir): void
	{
		$this->dir = $targetDir;
		$aggregate = $this->handleFiles($sourceFiles);

		foreach ($aggregate as $group => $fragments)
		{
			$this->writePuml(
				$this->dir . '/package-' . $group . '.puml',
				implode("\n", array_unique($this->removeIncludes($fragments))) . "\n"
			);
		}

		$this->render();
	}

	/**
	 * @param $sourceFiles
	 *
	 * @return array
	 */
	private function handleFiles($sourceFiles): array
	{
		$aggregate = [];

		foreach ($sourceFiles as $file)
		{
			$code = file_get_contents($file);

			foreach ($this->generateDiagramSource($code) as $group => $fragments)
			{
				$aggregate[$group] = array_merge($aggregate[$group] ?? [], $fragments);
			}
		}

		return $aggregate;
	}

	/**
	 * @param $code
	 *
	 * @return array
	 */
	private function generateDiagramSource($code): array
	{
		$identifier = '([\S]+)';

		$namespace = '';

		if (preg_match('~namespace\s+(.*?);~', $code, $match))
		{
			$namespace = trim(str_replace('\\', '.', $match[1]), '.') . '.';
		}

		$declaration = '(abstract\s+class|interface|trait|class)\s+' . $identifier;
		$extends     = '\s+extends\s+' . $identifier;
		$implements  = '\s+implements\s+' . $identifier . '(:?\s*,\s*' . $identifier . ')*';
		$pattern     = "~{$declaration}(:?{$extends})?(:?{$implements})?\s*\{~";

		if (!preg_match_all($pattern, $code, $matches, PREG_OFFSET_CAPTURE | PREG_SET_ORDER))
		{
			return [];
		}

		$classes = [];

		for ($i = 0, $n = count($matches); $i < $n; $i++)
		{
			if (isset($matches[$i + 1]))
			{
				$classes[$i] = substr($code, $matches[$i][0][1], $matches[$i + 1][0][1] - $matches[$i][0][1]);
			}
			else
			{
				$classes[$i] = substr($code, $matches[$i][0][1]);
			}
		}

		$aggregate = $this->prepareGroups($namespace);

		foreach ($matches as $i => $match)
		{
			$uml          = '';
			$currentClass = $namespace . $match[2][0];
			$filename     = $this->dir . '/class-' . $currentClass . '.puml';
			$uml          .= "{$match[1][0]} {$currentClass}\n";

			if (!empty($match[4][0]))
			{
				$uml .= $this->handleReference($namespace, $currentClass, '<|--', $match[4][0]);
			}

			if (!empty($match[6][0]))
			{
				$uml .= $this->handleReference($namespace, $currentClass, '<|..', $match[6][0]);
			}

			$this->writePuml($filename, $uml);
			$this->log("Generated class diagram for {$currentClass}");

			foreach ($aggregate as $level => $levelCode)
			{
				$aggregate[$level][] = $uml;
			}

			$this->handleMethods($currentClass, $classes[$i]);
		}

		return $aggregate;
	}

	/**
	 * @param $namespace
	 * @param $class
	 * @param $op
	 * @param $reference
	 *
	 * @return string
	 */
	private function handleReference($namespace, $class, $op, $reference): string
	{
		$reference = str_replace('\\', '.', $reference);
		$reference = $reference[0] === '.' ? substr($reference, 1) : $namespace . $reference;
		$uml       = "{$reference} {$op} {$class}\n";
		$uml       .= $this->includeReferencedClass($reference);

		return $uml;
	}

	/**
	 * @param $class
	 *
	 * @return string
	 */
	private function includeReferencedClass($class): string
	{
		$uml = '';

		if ($this->includeRef)
		{
			$file = "class-{$class}.puml";
			$uml  = "!include {$file}\n";

			if (!file_exists($this->dir . '/' . $file))
			{
				$this->findOrCreateDiagram($class, $file);
			}
		}

		return $uml;
	}

	/**
	 * @param $class
	 * @param $code
	 */
	private function handleMethods($class, $code): void
	{
		$pattern = "~@startuml\n(.*?)@enduml.*?(private|private|public)?\s+function\s+(\S+)\s*\(~sm";

		if (!preg_match_all($pattern, $code, $matches, PREG_SET_ORDER))
		{
			return;
		}

		foreach ($matches as $match)
		{
			$methodName = $class . '.' . $match[3];
			$this->writePuml($this->dir . '/seq-' . $methodName . '.puml', implode("\n", preg_split("~\s+\*\s+~", $match[1])) . "\n");
			$this->log("Extracted diagram for {$methodName}()");
		}
	}

	/**
	 * @param $filename
	 * @param $uml
	 */
	private function writePuml($filename, $uml): void
	{
		file_put_contents($filename, "@startuml\n!include skin.puml\n{$uml}@enduml\n");
	}

	/**
	 * @param string[] $uml
	 *
	 * @return array
	 */
	private function removeIncludes($uml): array
	{
		$uml = array_filter(
			explode("\n", implode("\n", $uml)),
			static function ($line) {
				return !preg_match('~^!include~', $line);
			}
		);

		return $uml;
	}

	/**
	 *
	 */
	private function render(): void
	{
		$this->log('Rendering ...');
		shell_exec("java -jar '{$this->jar}' -tsvg '{$this->dir}/*.puml'");
		$this->log('... done.');
	}

	/**
	 * @param $namespace
	 *
	 * @return array
	 */
	private function prepareGroups($namespace): array
	{
		$aggregate = ['global' => []];

		if ($namespace === '')
		{
			return $aggregate;
		}

		$currLevel = '';
		$parts     = explode('.', $namespace);

		while (!empty($parts))
		{
			$currLevel             = trim($currLevel . '.' . array_shift($parts), '.');
			$aggregate[$currLevel] = [];
		}

		return $aggregate;
	}

	/**
	 * @param string $string
	 */
	private function log(string $string): void
	{
		echo "UML: {$string}\n";
	}

	/**
	 * @param        $class
	 * @param string $file
	 */
	private function findOrCreateDiagram($class, string $file): void
	{
		if (file_exists($this->predefinedClasses . '/' . $file))
		{
			$this->log("Using predefined class diagram for {$class}");
			$this->copyWithIncludes($file);
		}
		else
		{
			$this->log("Generating class diagram for unknown {$class}");
			$this->writePuml($this->dir . '/' . $file, "class {$class} << (·,Transparent) >>\n");
		}
	}

	private function copyWithIncludes(string $file)
	{
		$uml = file_get_contents($this->predefinedClasses . '/' . $file);
		file_put_contents($this->dir . '/' . $file, $uml);

		if (preg_match_all('~class-(\w+).puml~', $uml, $matches, PREG_SET_ORDER))
		{
			foreach ($matches as $match)
			{
				$this->findOrCreateDiagram($match[1], $match[0]);
			}
		}
	}
}
