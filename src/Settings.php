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

namespace GreenCape\JoomlaCLI;

use GreenCape\JoomlaCLI\Utility\Array_;
use GreenCape\JoomlaCLI\Utility\Xml;

/**
 * Retrieve settings
 *
 * @since       Class available since Release __DEPLOY_VERSION__
 */
class Settings
{
    /**
     * @var string
     */
    private $projectName;

    public function __construct(string $projectName)
    {
        $this->projectName = $projectName;
    }

    /**
     * Get the environment settings
     *
     * @param  string  $actualXmlFile  The name of the XML file containing the environment definition
     * @param  string  $defaultDir     The directory containing the default environment definition (`default.xml`)
     *
     * @return array
     */
    public function environment(string $actualXmlFile, string $defaultDir): array
    {
        $target = basename($actualXmlFile, '.xml');

        $environment = [
            'name'     => $target,
            'server'   => [
                'type'   => 'nginx',
                'offset' => 'UTC',
                'tld'    => 'dev',
            ],
            'php'      => [
                'version' => 'latest',
            ],
            'cache'    => [
                'enabled' => 0,
                'time'    => 15,
                'handler' => 'file',
            ],
            'debug'    => [
                'system'   => 1,
                'language' => 1,
            ],
            'meta'     => [
                'description' => "Test installation for {$this->projectName} on Joomla! \${version}",
                'keywords'    => "{$this->projectName} Joomla Test",
                'showVersion' => 1,
                'showTitle'   => 1,
                'showAuthor'  => 1,
            ],
            'sef'      => [
                'enabled' => 0,
                'rewrite' => 0,
                'suffix'  => 0,
                'unicode' => 0,
            ],
            'session'  => [
                'lifetime' => 15,
                'handler'  => 'database',
            ],
            'joomla'   => [
                'version'    => 'latest',
                'sampleData' => 'data',
            ],
            'database' => [
                'driver' => 'mysqli',
                'name'   => 'joomla_test',
                'prefix' => '${target}_',
            ],
            'feeds'    => [
                'limit' => 10,
                'email' => 'author',
            ],
        ];

        $environment = Array_::merge(
            $environment,
            Xml::xmlProperty($defaultDir . '/default.xml', false, true)
        );
        $environment = Array_::merge(
            $environment,
            Xml::xmlProperty($actualXmlFile, false, true)
        );

        $environment['meta']['description'] = str_replace(
            '${version}',
            $environment['joomla']['version'],
            $environment['meta']['description']
        );
        $environment['database']['prefix']  = str_replace(
            '${target}',
            $environment['name'],
            $environment['database']['prefix']
        );

        if (in_array($environment['database']['driver'], ['mysqli', 'pdomysql'])) {
            $environment['database']['engine'] = 'mysql';
        } else {
            $environment['database']['engine'] = $environment['database']['driver'];
        }

        $database = $this->defaultDatabase($defaultDir);

        // Get the database info - use global values, if not provided with local environment
        $environment['database']['name'] = $environment['database']['name'] ?? $database[$environment['database']['engine']]['name'];

        return $environment;
    }

    /**
     * Load database environment, if provided, using default values for keys not defined in `$xmlFile`
     *
     * @param  string  $defaultDir  The directory containing the default database setting (`database.xml`)
     *
     * @return array
     */
    public function defaultDatabase(string $defaultDir): array
    {
        $database = [
            'mysql'      => [
                'version'      => 'latest',
                'name'         => 'joomla_test',
                'user'         => 'db_user',
                'password'     => 'db_pass',
                'rootPassword' => '',
            ],
            'postgresql' => [
                'version'  => 'latest',
                'name'     => 'joomla_test',
                'user'     => 'db_user',
                'password' => 'db_pass',
            ],
        ];

        if (file_exists($defaultDir . '/database.xml')) {
            $database = Array_::merge(
                $database,
                Xml::xmlProperty($defaultDir . '/database.xml', false, true)
            );
        }

        $database['mysql']['passwordOption'] = empty($database['mysql']['rootPassword'])
            ? ''
            : "-p'{$database['mysql']['rootPassword']}'";

        return $database;
    }
}
