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

namespace GreenCape\JoomlaCLI\Documentation\API\Strategy;

use GreenCape\JoomlaCLI\Fileset;

/**
 * @package     GreenCape\JoomlaCLI
 * @subpackage  Command
 * @since       Class available since Release __DEPLOY_VERSION__
 */
class Phpdoc implements APIGeneratorInterface
{
	/**
	 * The target directory for the documentation
	 *
	 * @var string
	 */
	private $target;

	/**
	 * Generate the API documentation
	 *
	 * @param string $title  The title for the documentation
	 * @param string $source The directory containing the source files
	 * @param string $target The target directory for the documentation
	 *
	 * @return mixed
	 */
	public function generate(string $title, string $source, string $target): void
	{
		$this->target = $target;

		$user = getmyuid() . ':' . getmygid();

		$command = "docker run"
		           . " --rm"
		           . " --user={$user}"
		           . " --volume={$source}:/app/io/source"
		           . " --volume={$target}:/app/io/target"
		           . " phpdoc/phpdoc"

		           . " --directory /app/io/source"
		           . " --target /app/io/target"
		           . " --title \"{$title}\""
		           . " --cache-folder /app/io/target/cache"
		           . " --template clean";

		passthru($command . ' 2>&1');
	}

	/**
	 * Embed the UML diagrams
	 *
	 * @param string $umlPath The path to the UML diagrams
	 *
	 * @return void
	 */
	public function embedUml(string $umlPath): void
	{
		$this->reflexive(
			(new Fileset($this->target . '/classes'))
				->include('**.html'),
			function ($content) use ($umlPath) {
				if (preg_match('~<h1><small>(.*?)</small>(.*?)</h1>~', $content, $match))
				{
					$namespace = $match[1];
					$classname = $match[2];

					$content = $this->replaceClassUML($content, 'class', $match[2], $umlPath);
					$content = $this->replaceMethodUML($content, $match[2], $umlPath);
				}

				return $content;
			}
		);
	}

	/**
	 * @param string $content The file content
	 * @param string $type    The unit type
	 * @param string $name    The unit name
	 * @param string $umlPath The path to the UML diagrams
	 *
	 * @return string
	 */
	private function replaceClassUML($content, string $type, string $name, string $umlPath): string
	{
		if (file_exists("{$this->target}/{$umlPath}/{$type}-{$name}.svg"))
		{
			$content = preg_replace(
				'~<h1><small>(.*?)</small>(.*?)</h1>\s+<p><em>.*?</em></p>~sm',
				"\$0<img src=\"../{$umlPath}/{$type}-{$name}.svg\" alt='Class Diagram'>",
				$content
			);
		}

		return $content;
	}

	/**
	 * @param string $content The file content
	 * @param string $name    The unit name
	 * @param string $umlPath The path to the UML diagrams
	 *
	 * @return string
	 */
	private function replaceMethodUML($content, string $name, string $umlPath): string
	{
		$content = preg_replace('~(Warning: count.*?on line \d+)~', '', $content);

		$content = preg_replace_callback(
			"~<article class=\"method\">\s*<h3[^>]*>(.*?)\(.*?</h3>.*?</article>\s*</div>\s*<aside[^>]*>(.*?)</aside>~sm",
			static function ($match) use ($name, $umlPath) {
				echo 'class-' . $name . '.' . $match[1] . "\n";
				// 1: method
				// 2: aside
				if (!preg_match('~<tr>\s*<th>\s*startuml\s*</th>\s*<td>.*?</td>\s*</tr>\s*<tr>\s*<th>\s*enduml\s*</th>\s*<td>\s*</td>\s*</tr>~sm', $match[2], $m))
				{
					return $match[0];
				}

				$replace = str_replace($m[0], '<tr><th>UML</th><td>see left</td></tr>', $match[0]);

				return str_replace('</article>', "<h4>UML</h4><img src=\"../{$umlPath}/seq-{$name}.{$match[1]}.svg\" alt=\"Sequence Diagram\"></article>", $replace);
			},
			$content
		);

		return $content;
	}

	/**
	 * @param Fileset|string $fileset
	 * @param callable       $filter
	 */
	private function reflexive($fileset, callable $filter): void
	{
		if (is_string($fileset))
		{
			$this->copyFile($fileset, $fileset, $filter);

			return;
		}

		foreach ($fileset->getFiles() as $file)
		{
			$this->copyFile($file, $file, $filter);
		}
	}

	/**
	 * @param string        $file
	 * @param string        $toFile
	 * @param callable|null $filter
	 */
	private function copyFile(string $file, string $toFile, callable $filter = null): void
	{
		if (is_dir($file))
		{
			return;
		}

		if (is_callable($filter))
		{
			file_put_contents($toFile, $filter(file_get_contents($file)));
		}
	}
}
