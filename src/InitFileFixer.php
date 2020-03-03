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

namespace GreenCape\JoomlaCLI;

/**
 * @since  Class available since Release __DEPLOY_VERSION__
 */
class InitFileFixer
{
    /**
     * @param  string  $file
     *
     * @return int
     */
    public function fix(string $file): int
    {
        $this->log("Formatting file {$file}");
        $tmp = tempnam(dirname($file), 'tmp');

        $src = fopen($file, 'rb');
        if (!$src) {
            $this->log("Unable to open {$file}", 'warning');

            return 1;
        }
        $dst = fopen($tmp, 'wb');
        if (!$dst) {
            $this->log("Unable to open {$tmp}", 'warning');
            fclose($src);

            return 1;
        }

        $prefix     = '';
        $buffer     = '';
        $insidePara = false;
        $insideStat = false;
        $incomplete = false;
        do {
            $line = trim(fgets($src));
            if (empty($line)) {
                $this->dump('Skipping empty line', $line);
            } elseif (preg_match('~^(#|--)~', $line)) {
                $this->dump('Skipping comment', $line);
            } elseif ($incomplete) {
                $this->dump('Incomplete:', $line);
                $prefix     = trim("$prefix $line");
                $incomplete = !preg_match('~VALUES$~i', $line);
            } elseif (preg_match('~;$~', $line)) {
                $this->dump("Encountered ';':", $line);
                $buffer = trim("$buffer $prefix $line");
                fwrite($dst, "$buffer\n");
                $buffer     = $prefix = '';
                $insidePara = false;
                $insideStat = false;
            } elseif ($insidePara) {
                $this->dump('inside (), adding:', $line);
                $buffer = trim("$buffer $prefix $line");
            } elseif (preg_match('~\($~', $line)) {
                $this->dump("Encountered '(':", $line);
                $buffer     = trim("$buffer $prefix $line");
                $insidePara = true;
            } elseif (preg_match('~^(INSERT|REPLACE)~i', $line, $match)) {
                $this->dump("Multiline '{$match[1]}':", $line);
                $prefix     = $line;
                $insideStat = true;
                $incomplete = !preg_match('~VALUES$~i', $line);
            } elseif ($insideStat && preg_match('~^\((.*)\)\s*,~', $line, $match)) {
                $this->dump('inside statement, adding:', $line);
                $buffer .= trim("$prefix ($match[1]);");
                fwrite($dst, "$buffer\n");
                $buffer = '';
            } else {
                $this->dump('Line not handled:', $line, 'warning');
                $buffer = trim("$buffer $line");
            }
        } while (!feof($src));

        fclose($dst);
        fclose($src);

        copy($tmp, $file);
        unlink($tmp);

        return 0;
    }

    /**
     * @param  string  $message
     * @param  string  $level
     */
    private function log(string $message, string $level = 'debug'): void
    {
        #echo "$message\n";
    }

    /**
     * @param        $label
     * @param        $value
     * @param  null  $level
     */
    private function dump($label, $value, $level = null): void
    {
        $this->log(sprintf('%-30s %s', $label, substr($value, 0, 80)), $level ?? 'debug');
    }
}
