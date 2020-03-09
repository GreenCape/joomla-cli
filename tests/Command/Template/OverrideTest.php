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

namespace UnitTest\Command;

use Exception;
use GreenCape\JoomlaCLI\Command\Core\DownloadCommand;
use GreenCape\JoomlaCLI\Command\Template\OverrideCommand;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\NullOutput;
use UnitTest\Driver\JoomlaPackagesTrait;

/**
 * Class OverrideTest
 *
 * @testdox Command `template:override` ...
 */
class OverrideTest extends TestCase
{
    use JoomlaPackagesTrait;

    public function joomlaPackagesWithout10()
    {
        $packages = $this->joomlaPackages();
        unset($packages['1.0']);

        return $packages;
    }

    /**
     * @testdox      ... creates override files for Joomla! $release (tested with Joomla! $short)
     *
     * @param  string  $path
     * @param  string  $release
     * @param  string  $short
     * @param  string  $long
     *
     * @throws Exception
     * @dataProvider joomlaPackagesWithout10
     */
    public function testOverride($path, $release, $short, $long): void
    {
        $command = new DownloadCommand();
        $output  = new NullOutput();

        $command->run(new StringInput("--joomla=tmp/$path $short"), $output);

        $command = new OverrideCommand();
        $output  = new NullOutput();

        $command->run(new StringInput("--joomla=tmp/$path system"), $output);

        $contents = glob("tmp/$path/templates/system/html/*");

        $this->assertTrue(count($contents) > 2);
    }
}
