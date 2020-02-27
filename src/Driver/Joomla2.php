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
 * @since           File available since Release 0.1.0
 */

namespace GreenCape\JoomlaCLI\Driver;

use Exception;
use JFactory;

/**
 * Version specific methods
 *
 * @since  Class available since Release 0.1.0
 */
class Joomla2Driver extends JoomlaDriver
{
    /**
     * Setup the environment
     *
     * @param  string  $application  The application, eg., 'site' or 'administration'
     *
     * @return  void
     * @throws Exception
     */
    public function setupEnvironment($application = 'site'): void
    {
        define('DS', DIRECTORY_SEPARATOR);

        parent::setupEnvironment($application);

        jimport('joomla.application.component.helper');
    }

    /**
     * Set a configuration value.
     *
     * @param  string  $key    The key
     * @param  mixed   $value  The value
     *
     * @return  mixed  The value
     */
    public function setCfg($key, $value)
    {
        return JFactory::getConfig()->set($key, $value);
    }

    /**
     * Gets a configuration value.
     *
     * @param  string  $key  The name of the value to get
     *
     * @return  mixed  The value
     */
    public function getCfg($key)
    {
        return JFactory::getConfig()->get($key);
    }

    /**
     * Get the queries for creating a super user account
     *
     * @param  string  $adminUser
     * @param  string  $adminPassword
     * @param  string  $adminEmail
     *
     * @return array SQL statements
     */
    public function getRootAccountCreationQuery($adminUser, $adminPassword, $adminEmail): array
    {
        $salt      = $this->genRandomPassword(32);
        $crypt     = $this->getCryptedPassword($adminPassword, $salt);
        $cryptPass = $crypt . ':' . $salt;

        $nullDate    = '0000-00-00 00:00:00';
        $installDate = date('Y-m-d H:i:s');

        /** @todo Escape admin* values */
        return [
            "REPLACE INTO `#__users` SET id=42, name='Super User', username='$adminUser', email='$adminEmail', password='$cryptPass', usertype='deprecated', block=0, sendEmail=1, registerDate='$installDate', lastvisitDate='$nullDate', activation='', params=''",
            "REPLACE INTO `#__user_usergroup_map` SET user_id=42, group_id=8",
        ];
    }

    /**
     * Generate a random password
     *
     * Taken from Joomla! 1.7.5 installer
     *
     * @param  integer  $length  Length of the password to generate
     *
     * @return  string  Random Password
     */
    private function genRandomPassword($length = 8): string
    {
        $salt = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $len  = strlen($salt);
        $pass = '';

        for ($i = 0; $i < $length; $i++) {
            $pass .= $salt[mt_rand(0, $len - 1)];
        }

        return $pass;
    }

    /**
     * Formats a password using the current encryption.
     *
     * Taken from Joomla! 1.7.5 installer
     *
     * @param  string   $plaintext      The plaintext password to encrypt.
     * @param  string   $salt           The salt to use to encrypt the password. []
     *                                  If not present, a new salt will be
     *                                  generated.
     * @param  string   $encryption     The kind of pasword encryption to use.
     *                                  Defaults to md5-hex.
     * @param  boolean  $show_encrypt   Some password systems prepend the kind of
     *                                  encryption to the crypted password ({SHA},
     *                                  etc). Defaults to false.
     *
     * @return  string  The encrypted password.
     */
    private function getCryptedPassword($plaintext, $salt = '', $encryption = 'md5-hex', $show_encrypt = false): string
    {
        $salt = $this->getSalt($encryption, $salt, $plaintext);

        switch ($encryption) {
            case 'plain' :
                return $plaintext;

            case 'sha' :
                $encrypted = base64_encode(mhash(MHASH_SHA1, $plaintext));

                return ($show_encrypt) ? '{SHA}' . $encrypted : $encrypted;

            case 'crypt' :
            case 'crypt-des' :
            case 'crypt-md5' :
            case 'crypt-blowfish' :
                return ($show_encrypt ? '{crypt}' : '') . crypt($plaintext, $salt);

            case 'md5-base64' :
                $encrypted = base64_encode(mhash(MHASH_MD5, $plaintext));

                return ($show_encrypt) ? '{MD5}' . $encrypted : $encrypted;

            case 'ssha' :
                $encrypted = base64_encode(mhash(MHASH_SHA1, $plaintext . $salt) . $salt);

                return ($show_encrypt) ? '{SSHA}' . $encrypted : $encrypted;

            case 'smd5' :
                $encrypted = base64_encode(mhash(MHASH_MD5, $plaintext . $salt) . $salt);

                return ($show_encrypt) ? '{SMD5}' . $encrypted : $encrypted;

            case 'aprmd5' :
                $length  = strlen($plaintext);
                $context = $plaintext . '$apr1$' . $salt;
                $binary  = $this->bin(md5($plaintext . $salt . $plaintext));

                for ($i = $length; $i > 0; $i -= 16) {
                    $context .= substr($binary, 0, ($i > 16 ? 16 : $i));
                }

                for ($i = $length; $i > 0; $i >>= 1) {
                    $context .= ($i & 1) ? chr(0) : $plaintext[0];
                }

                $binary = $this->bin(md5($context));

                for ($i = 0; $i < 1000; $i++) {
                    $new = ($i & 1) ? $plaintext : substr($binary, 0, 16);

                    if ($i % 3) {
                        $new .= $salt;
                    }

                    if ($i % 7) {
                        $new .= $plaintext;
                    }

                    $new    .= ($i & 1) ? substr($binary, 0, 16) : $plaintext;
                    $binary = $this->bin(md5($new));
                }

                $p = [];

                for ($i = 0; $i < 5; $i++) {
                    $k = $i + 6;
                    $j = $i + 12;
                    if ($j == 16) {
                        $j = 5;
                    }
                    $p[] = $this->toAPRMD5((ord($binary[$i]) << 16) | (ord($binary[$k]) << 8) | (ord($binary[$j])),
                        5);
                }

                return '$apr1$' . $salt . '$' . implode('', $p) . $this->toAPRMD5(ord($binary[11]), 3);

            case 'md5-hex' :
            default :
                $encrypted = ($salt) ? md5($plaintext . $salt) : md5($plaintext);

                return ($show_encrypt) ? '{MD5}' . $encrypted : $encrypted;
        }
    }

    /**
     * Returns a salt for the appropriate kind of password encryption.
     * Optionally takes a seed and a plaintext password, to extract the seed
     * of an existing password, or for encryption types that use the plaintext
     * in the generation of the salt.
     *
     * Taken from Joomla! 1.7.5 installer
     *
     * @param  string  $encryption   The kind of pasword encryption to use.
     *                               Defaults to md5-hex.
     * @param  string  $seed         The seed to get the salt from (probably a
     *                               previously generated password). Defaults to
     *                               generating a new seed.
     * @param  string  $plaintext    The plaintext password that we're generating
     *                               a salt for. Defaults to none.
     *
     * @return  string  The generated or extracted salt.
     */
    private function getSalt($encryption = 'md5-hex', $seed = '', $plaintext = ''): string
    {
        switch ($encryption) {
            case 'crypt' :
            case 'crypt-des' :
                return $seed
                    ? substr(preg_replace('|^{crypt}|i', '', $seed), 0, 2)
                    : substr(md5(mt_rand()), 0, 2);

            case 'crypt-md5' :
                return $seed
                    ? substr(preg_replace('|^{crypt}|i', '', $seed), 0, 12)
                    : '$1$' . substr(md5(mt_rand()), 0, 8) . '$';

            case 'crypt-blowfish' :
                return $seed
                    ? substr(preg_replace('|^{crypt}|i', '', $seed), 0, 16)
                    : '$2$' . substr(md5(mt_rand()), 0, 12) . '$';

            case 'ssha' :
                return $seed
                    ? substr(preg_replace('|^{SSHA}|', '', $seed), -20)
                    : mhash_keygen_s2k(MHASH_SHA1, $plaintext, substr(pack('h*', md5(mt_rand())), 0, 8), 4);

            case 'smd5' :
                return $seed
                    ? substr(preg_replace('|^{SMD5}|', '', $seed), -16)
                    : mhash_keygen_s2k(MHASH_MD5, $plaintext, substr(pack('h*', md5(mt_rand())), 0, 8), 4);

            case 'aprmd5' :
                /* 64 characters that are valid for APRMD5 passwords. */
                $APRMD5 = './0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';

                if ($seed) {
                    return substr(preg_replace('/^\$apr1\$(.{8}).*/', '\\1', $seed), 0, 8);
                }

                $salt = '';
                for ($i = 0; $i < 8; $i++) {
                    $salt .= $APRMD5[rand(0, 63)];
                }

                return $salt;

            default :
                $salt = '';
                if ($seed) {
                    $salt = $seed;
                }

                return $salt;
        }
    }

    /**
     * Converts hexadecimal string to binary data.
     *
     * Taken from Joomla! 1.7.5 installer
     *
     * @param  string  $hex  Hex data.
     *
     * @return  string  Binary data.
     */
    private function bin($hex): string
    {
        $bin    = '';
        $length = strlen($hex);

        for ($i = 0; $i < $length; $i += 2) {
            $tmp = sscanf(substr($hex, $i, 2), '%x');
            $bin .= chr(array_shift($tmp));
        }

        return $bin;
    }

    /**
     * Converts to allowed 64 characters for APRMD5 passwords.
     *
     * Taken from Joomla! 1.7.5 installer
     *
     * @param  string   $value
     * @param  integer  $count
     *
     * @return  string  $value converted to the 64 MD5 characters.
     */
    private function toAPRMD5($value, $count): string
    {
        /* 64 characters that are valid for APRMD5 passwords. */
        $APRMD5 = './0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';

        $aprmd5 = '';
        $count  = abs($count);

        while (--$count) {
            $aprmd5 .= $APRMD5[$value & 0x3f];
            $value  >>= 6;
        }

        return $aprmd5;
    }
}
