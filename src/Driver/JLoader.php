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

use GreenCape\JoomlaCLI\Documentation\UML\ClassNameCollector;
use Psr\Log\LoggerInterface;

/**
 * Class JLoader
 *
 * Fake version of Joomla!'s JLoader registering aliases from a class map to a UMLCollectorInterface
 *
 * @since  Class available since Release __DEPLOY_VERSION__
 */
class JLoader
{
    /** @var string */
    public static $separator;

    /** @var ClassNameCollector */
    public static $collector;

    /** @var LoggerInterface */
    public static $logger;

    public static function registerAlias($alias, $original, $version): void
    {
        self::$logger->debug("Registering alias {$alias} for {$original}");
        $original = str_replace('\\', self::$separator, $original);
        self::$collector->add(
            $alias,
            "class {$alias} << alias >>\n{$original} == {$alias}\n",
            [
                $original,
            ]
        );
    }
}
