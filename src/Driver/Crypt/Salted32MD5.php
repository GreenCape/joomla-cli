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

/**
 * Version specific password encryption
 *
 * @since  Class available since Release __DEPLOY_VERSION__
 */
class Salted32MD5 extends Salted16MD5
{
    /**
     * @return string
     */
    public function createSalt(): string
    {
        $salt   = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $base   = strlen($salt);
        $pass   = '';
        $length = 32;

        $random = $this->genRandomBytes($length + 1);
        $shift  = ord($random[0]);

        for ($i = 1; $i <= $length; ++$i) {
            $pass  .= $salt[($shift + ord($random[$i])) % $base];
            $shift += ord($random[$i]);
        }

        return $pass;
    }

    /**
     * Generate random bytes.
     *
     * Taken from Joomla 1.5.26 installer.
     *
     * @param  integer  $length  Length of the random data to generate
     *
     * @return  string  Random binary data
     */
    private function genRandomBytes($length = 16): string
    {
        $sslStr = '';

        $bitsPerRound  = 2;
        $maxTimeMicro  = 400;
        $shaHashLength = 20;
        $randomStr     = '';
        $total         = $length;

        $urandom = false;
        $handle  = null;
        if (function_exists('stream_set_read_buffer') && @is_readable('/dev/urandom')) {
            $handle = @fopen('/dev/urandom', 'rb');
            if ($handle) {
                $urandom = true;
            }
        }

        while ($length > strlen($randomStr)) {
            $bytes   = ($total > $shaHashLength) ? $shaHashLength : $total;
            $total   -= $bytes;
            $entropy = rand() . uniqid(mt_rand(), true) . $sslStr;
            $entropy .= implode('', @fstat(fopen(__FILE__, 'r')));
            $entropy .= memory_get_usage();
            $sslStr  = '';
            if ($urandom) {
                stream_set_read_buffer($handle, 0);
                $entropy .= @fread($handle, $bytes);
            } else {
                $samples  = 3;
                $duration = 0;
                for ($pass = 0; $pass < $samples; ++$pass) {
                    $microStart = microtime(true) * 1000000;
                    $hash       = sha1(mt_rand(), true);
                    for ($count = 0; $count < 50; ++$count) {
                        $hash = sha1($hash, true);
                    }
                    $microEnd = microtime(true) * 1000000;
                    $entropy  .= $microStart . $microEnd;
                    if ($microStart > $microEnd) {
                        $microEnd += 1000000;
                    }
                    $duration += $microEnd - $microStart;
                }
                $duration = $duration / $samples;

                $rounds = (int)(($maxTimeMicro / $duration) * 50);

                $iter = $bytes * (int)ceil(8 / $bitsPerRound);
                for ($pass = 0; $pass < $iter; ++$pass) {
                    $microStart = microtime(true);
                    $hash       = sha1(mt_rand(), true);
                    for ($count = 0; $count < $rounds; ++$count) {
                        $hash = sha1($hash, true);
                    }
                    $entropy .= $microStart . microtime(true);
                }
            }

            $randomStr .= sha1($entropy, true);
        }

        if ($urandom) {
            @fclose($handle);
        }

        return substr($randomStr, 0, $length);
    }
}
