<?php /** @noinspection PhpUndefinedMethodInspection */

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
use JText;

/**
 * Version specific methods
 *
 * @since  Class available since Release 0.1.0
 */
class Joomla1Dot5Driver extends JoomlaDriver
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
        if ($application !== 'site') {
            $this->basePath .= '/' . $application;
        }

        $server  = [
            'HTTP_HOST'       => 'undefined',
            'HTTP_USER_AGENT' => 'undefined',
            'REQUEST_METHOD'  => 'GET',
        ];
        $_SERVER = array_merge($_SERVER, $server);

        define('JPATH_BASE', $this->basePath);
        define('DS', DIRECTORY_SEPARATOR);

        require_once JPATH_BASE . '/includes/defines.php';
        /** @noinspection PhpUndefinedConstantInspection - defined in defines.php */
        require_once JPATH_LIBRARIES . '/loader.php';

        spl_autoload_register('__autoload');

        require_once JPATH_BASE . '/includes/framework.php';

        if ($application === 'administrator') {
            require_once JPATH_BASE . '/includes/helper.php';
            require_once JPATH_BASE . '/includes/toolbar.php';

            // JUri uses $_SERVER['HTTP_HOST'] without check
            $_SERVER['HTTP_HOST'] = 'CLI';
        }

        jimport('joomla.installer.installer');
        jimport('joomla.installer.helper');

        $mainframe = JFactory::getApplication($application);
        $mainframe->initialise();
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
        return JFactory::getConfig()->setValue('config.' . $key, $value);
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
        return JFactory::getConfig()->getValue('config.' . $key);
    }

    /**
     *
     * @param  object  $manifest
     *
     * @return array
     */
    public function getExtensionInfo($manifest): array
    {
        $data                = [];
        $manifest            = $manifest->document;
        $data['type']        = (string)$manifest->attributes('type');
        $data['extension']   = (string)$manifest->name[0]->data();
        $data['name']        = JText::_($manifest->name[0]->data());
        $data['version']     = (string)$manifest->version[0]->data();
        $data['description'] = JText::_($manifest->description[0]->data());

        return $data;
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
            "INSERT INTO `#__users` VALUES (62, 'Administrator', '$adminUser', '$adminEmail', '$cryptPass', 'Super Administrator', 0, 1, 25, '$installDate', '$nullDate', '', '')",
            "INSERT INTO `#__core_acl_aro` VALUES (10,'users','62',0,'Administrator',0)",
            "INSERT INTO `#__core_acl_groups_aro_map` VALUES (25,'',10)",
        ];
    }

    /**
     * Generate a random password.
     *
     * Taken from Joomla 1.5.26 installer.
     *
     * @param  int  $length  Length of the password to generate
     *
     * @return    string            Random Password
     */
    private function genRandomPassword($length = 8): ?string
    {
        $salt = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $base = strlen($salt);
        $pass = '';

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
