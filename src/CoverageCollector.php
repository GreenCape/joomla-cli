<?php

namespace GreenCape\JoomlaCLI;

use SebastianBergmann\CodeCoverage\CodeCoverage;

class CoverageCollector
{
    private $data      = [];
    private $tests     = [];
    private $whiteList = [];
    private $blackList = [];

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @return array
     */
    public function getTests(): array
    {
        return $this->tests;
    }

    /**
     * @return array
     */
    public function getWhiteList(): array
    {
        return $this->whiteList;
    }

    /**
     * @return array
     */
    public function getBlackList(): array
    {
        return $this->blackList;
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
     * @param $tests
     */
    public function setTests($tests): void
    {
        $this->tests = array_merge($this->tests, $tests);
    }

    /**
     * @return CoverageCollector
     */
    public function filter(): self
    {
        return $this;
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
