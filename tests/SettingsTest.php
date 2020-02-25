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
 * @subpackage      Unittests
 * @author          Niels Braczek <nbraczek@bsds.de>
 * @copyright   (C) 2012-2019 GreenCape, Niels Braczek <nbraczek@bsds.de>
 * @license         http://opensource.org/licenses/MIT The MIT license (MIT)
 * @link            http://greencape.github.io
 * @since           File available since Release 1.1.0
 */

namespace GreenCapeTest;

use GreenCape\JoomlaCLI\Settings;
use PHPUnit\Framework\TestCase;

class SettingsTest extends TestCase
{
    public function testDefaultDatabase(): void
    {
        $settings = new Settings('Test');

        $expected = [
            'mysql'      => [
                'version'        => 'latest',
                'name'           => 'joomla_test',
                'user'           => 'sqladmin',
                'password'       => 'sqladmin',
                'rootPassword'   => 'root',
                'passwordOption' => "-p'root'",
            ],
            'postgresql' => [
                'version'  => 'latest',
                'name'     => 'joomla_test',
                'user'     => 'sqladmin',
                'password' => 'sqladmin',
            ],
        ];

        $database = $settings->defaultDatabase(__DIR__ . '/fixtures/settings');

        $this->assertEquals($expected, $database);
    }

    public function testEnvironment(): void
    {
        $settings = new Settings('Test');

        $expected = [
            'name'     => 'joomla-latest',
            'server'   => [
                'type'   => 'nginx',
                'offset' => 'UTC',
                'tld'    => 'dev',
            ],
            'php'      => [
                'version' => '7.1',
            ],
            'cache'    => [
                'enabled' => '0',
                'time'    => '15',
                'handler' => 'file',
            ],
            'debug'    => [
                'system'   => '1',
                'language' => '1',
            ],
            'meta'     => [
                'description' => 'Test installation',
                'keywords'    => '',
                'showVersion' => '1',
                'showTitle'   => '1',
                'showAuthor'  => '1',
            ],
            'sef'      => [
                'enabled' => '0',
                'rewrite' => '0',
                'suffix'  => '0',
                'unicode' => '0',
            ],
            'session'  => [
                'lifetime' => '15',
                'handler'  => 'database',
            ],
            'joomla'   => [
                'version'    => '3',
                'sampleData' => 'data',
            ],
            'database' => [
                'driver' => 'mysql',
                'name'   => 'joomla3',
                'prefix' => 'j3lat_',
                'engine' => 'mysql',
            ],
            'feeds'    => [
                'limit' => '10',
                'email' => 'author',
            ],
        ];

        $environment = $settings->environment(
            __DIR__ . '/fixtures/settings/joomla-latest.xml',
            __DIR__ . '/fixtures/settings'
        );

        $this->assertEquals($expected, $environment);
    }
}
