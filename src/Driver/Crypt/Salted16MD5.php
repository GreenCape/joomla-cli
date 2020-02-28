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

namespace GreenCape\JoomlaCLI\Driver\Crypt;

use Exception;
use JText;

/**
 * Version specific password encryption
 *
 * @since  Class available since Release __DEPLOY_VERSION__
 */
class Salted16MD5 implements CryptInterface
{
    /**
     * @param  string  $password
     * @param  string  $salt
     *
     * @return string
     */
    public function encryptPassword(string $password, string $salt): string
    {
        $crypt = md5($password . $salt);

        return $crypt . ':' . $salt;
    }

    /**
     * @return string
     */
    public function createSalt(): string
    {
        return $this->mosMakePassword(16);
    }

    /**
     * @param  string  $password
     * @param  string  $hash
     *
     * @return bool
     */
    public function verifyPassword(string $password, string $hash): bool
    {
        [$dummy, $salt] = explode(':', $hash);

        return $hash === $this->encryptPassword($password, $salt);
    }

    /**
     * Generate a random password of given length.
     *
     * Taken from Joomla 1.0.15 installer.
     *
     * @param  int  $length
     *
     * @return string
     */
    private function mosMakePassword($length): string
    {
        $salt = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $len  = strlen($salt);
        $pass = '';

        mt_srand(10000000 * (double)microtime());

        for ($i = 0; $i < $length; $i++) {
            $pass .= $salt[mt_rand(0, $len - 1)];
        }

        return $pass;
    }
}
