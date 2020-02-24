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

namespace GreenCape\JoomlaCLI;

use SebastianBergmann\CodeCoverage\CodeCoverage;

/**
 * Class CoverageCollector
 *
 * @since  Class available since Release __DEPLOY_VERSION__
 */
class CoverageCollector
{
    private $data      = [];
    private $tests     = [];
    private $whiteList = [];
    private $blackList = [];

    /**
     * @return CoverageCollector
     */
    public function filter(): self
    {
        return $this;
    }

    /**
     * @param  CoverageCollector  $coverage
     *
     * @return CoverageCollector
     */
    public function merge(CoverageCollector $coverage): self
    {
        $this->setData($coverage->getData());
        $this->setTests($coverage->getTests());
        $this->setWhitelistedFiles($coverage->getWhiteList());
        $this->setBlacklistedFiles($coverage->getBlackList());

        return $this;
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    public function setData($data): void
    {
        foreach ($data as $file => $lines) {
            if (!file_exists($file)) {
                continue;
            }

            foreach ($lines as $line => $tests) {
                if (!is_array($tests)) {
                    continue;
                }
                if (!isset($this->data[$file][$line])) {
                    $this->data[$file][$line] = [];
                }
                $this->data[$file][$line] = array_unique(array_merge($this->data[$file][$line], $tests));
            }
        }
    }

    /**
     * @return array
     */
    public function getTests(): array
    {
        return $this->tests;
    }

    /**
     * @param $tests
     */
    public function setTests($tests): void
    {
        $this->tests = array_merge($this->tests, $tests);
    }

    /**
     * @param $list
     *
     * @return CoverageCollector
     */
    public function setWhitelistedFiles($list): self
    {
        $this->whiteList = array_merge($this->whiteList, $list);

        return $this;
    }

    /**
     * @return array
     */
    public function getWhiteList(): array
    {
        return $this->whiteList;
    }

    /**
     * @param $list
     *
     * @return CoverageCollector
     */
    public function setBlacklistedFiles($list): self
    {
        $this->blackList = array_merge($this->blackList, $list);

        return $this;
    }

    /**
     * @return array
     */
    public function getBlackList(): array
    {
        return $this->blackList;
    }

    /**
     * @return CodeCoverage
     */
    public function coverage(): CodeCoverage
    {
        $coverage = new CodeCoverage;
        $coverage->setData($this->data);
        $coverage->setTests($this->tests);
        $filter = $coverage->filter();
        #$filter->setBlacklistedFiles($this->blackList);
        $filter->setWhitelistedFiles($this->whiteList);

        return $coverage;
    }
}
