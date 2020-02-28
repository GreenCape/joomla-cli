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
 * @since           File available since Release __DEPLOY_VERSION_
 */

namespace GreenCapeTest\Driver;

use GreenCape\JoomlaCLI\Driver\Factory;
use League\Flysystem\Adapter\Local;
use League\Flysystem\FileNotFoundException;
use League\Flysystem\Filesystem;
use PHPUnit\Framework\TestCase;

/**
 * Class CryptTest
 *
 * @testdox Encryption Driver ...
 */
class CryptTest extends TestCase
{
    use JoomlaPackagesTrait;

    public function passwords(): array
    {
        $packages = $this->joomlaPackages();

        return [
            'admin-1.0' => [
                'plain'     => 'admin',
                'encrypted' => 'e8d876703ae04a3fe6c4868ff296fb9f:99SknoS4CFHGWhkOtk8cNTIDXR0bSUvN',
                'path'      => $packages['1.0'][0],
                'version'   => $packages['1.0'][1],
            ],
            'user-1.0'  => [
                'plain'     => 'user',
                'encrypted' => '92b46d92fc58eb86e92a8c796febeb34:fXySsDEWvyiIg0ifkftgTkrXzmviMvC3',
                'path'      => $packages['1.0'][0],
                'version'   => $packages['1.0'][1],
            ],
            'admin-1.5' => [
                'plain'     => 'admin',
                'encrypted' => 'e8d876703ae04a3fe6c4868ff296fb9f:99SknoS4CFHGWhkOtk8cNTIDXR0bSUvN',
                'path'      => $packages['1.5'][0],
                'version'   => $packages['1.5'][1],
            ],
            'user-1.5'  => [
                'plain'     => 'user',
                'encrypted' => '92b46d92fc58eb86e92a8c796febeb34:fXySsDEWvyiIg0ifkftgTkrXzmviMvC3',
                'path'      => $packages['1.5'][0],
                'version'   => $packages['1.5'][1],
            ],
            'admin-2.5' => [
                'plain'     => 'admin',
                'encrypted' => 'e8d876703ae04a3fe6c4868ff296fb9f:99SknoS4CFHGWhkOtk8cNTIDXR0bSUvN',
                'path'      => $packages['2.5'][0],
                'version'   => $packages['2.5'][1],
            ],
            'user-2.5'  => [
                'plain'     => 'user',
                'encrypted' => '92b46d92fc58eb86e92a8c796febeb34:fXySsDEWvyiIg0ifkftgTkrXzmviMvC3',
                'path'      => $packages['2.5'][0],
                'version'   => $packages['2.5'][1],
            ],
            'admin-3'   => [
                'plain'     => 'admin',
                'encrypted' => '$2y$10$sZhmnIOc9ZWK39zwHS.Ju.zTlXnKi/yHVZjVKM.eUbjtA/TYFC98q',
                'path'      => $packages['3.5'][0],
                'version'   => $packages['3.5'][1],
            ],
            'user-3'    => [
                'plain'     => 'user',
                'encrypted' => '$2y$10$Jrt7Zu.d3K9K5ajP8VjHx.Xj9Ale8eJUE3WiEAlbgHOWhthZZXm3a',
                'path'      => $packages['3.5'][0],
                'version'   => $packages['3.5'][1],
            ],
        ];
    }

    /**
     * @testdox      ... generates correct password hash for '$plainPassword' for Joomla! $version
     *
     * @param $plainPassword
     * @param $encryptedPassword
     * @param $path
     *
     * @dataProvider passwords
     * @throws FileNotFoundException
     */
    public function testCreatePassword($plainPassword, $encryptedPassword, $path, $version): void
    {
        $filesystem = new Filesystem(new Local('tests/fixtures/' . $path));
        $driver     = (new Factory())->create($filesystem);
        $crypt      = $driver->crypt();

        $parts  = explode(':', $encryptedPassword);
        $salt   = count($parts) === 2 ? $parts[1] : '';
        $actual = $crypt->encryptPassword($plainPassword, $salt);

        $this->assertTrue($crypt->verifyPassword($plainPassword, $encryptedPassword));
        $this->assertTrue($crypt->verifyPassword($plainPassword, $actual));
    }
}
