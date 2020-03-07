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
 * @since           File available since Release 0.1.0
 */

namespace UnitTest\Driver;

use Dotenv\Dotenv;
use GreenCape\JoomlaCLI\Driver\Environment;
use PHPUnit\Framework\TestCase;

/**
 * Class EnvironmentTest
 *
 * @testdox Environment Driver ...
 */
class EnvironmentTest extends TestCase
{
    /**
     * @testdox ... gives locally defined vars precedence before variables defined in the `.env` file
     */
    public function testExplicitOverride(): void
    {
        putenv('TEST_VAR="Local environment"');

        $env = Dotenv::createImmutable(dirname(__DIR__) . '/fixtures');
        $env->load();

        $this->assertEquals('Local environment', trim(getenv('TEST_VAR'), '"'));
    }

    /**
     * @testdox ... merges definition file an environment settings, giving precedence to the latter
     */
    public function testMerge(): void
    {
        $env = new Environment('tests/fixtures/env.json', 'tests/fixtures/.env2');

        $this->assertEquals('.env file', $env->joomla['meta']['description']);
    }
}
