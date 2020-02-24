<?php

namespace GreenCape\JoomlaCLI;

use RuntimeException;
use SebastianBergmann\CodeCoverage\CodeCoverage;
use SebastianBergmann\CodeCoverage\Report\Clover;
use SebastianBergmann\CodeCoverage\Report\Crap4j;
use SebastianBergmann\CodeCoverage\Report\Html\Facade;
use SebastianBergmann\CodeCoverage\Report\PHP;
use SebastianBergmann\CodeCoverage\Report\Text;

/**
 * Class CoverageMerger
 *
 * @since  Class available since Release __DEPLOY_VERSION__
 */
class CoverageMerger
{
    /**
     * All fileset objects assigned to this task
     *
     * @var FileSet[]
     */
    private $filesets = [];

    private $pattern;
    private $replace;

    private $clover;
    private $crap4j;
    private $html;
    private $php;
    private $text;

    /**
     * @param  string  $pattern
     *
     * @return CoverageMerger
     */
    public function pattern($pattern): self
    {
        $this->pattern = '~' . str_replace('~', '\\~', $pattern) . '~';

        return $this;
    }

    /**
     * @param  string  $replace
     *
     * @return CoverageMerger
     */
    public function replace($replace): self
    {
        $this->replace = $replace;

        return $this;
    }

    /**
     * @param  string  $clover
     *
     * @return CoverageMerger
     */
    public function clover($clover): self
    {
        $this->clover = $clover;

        return $this;
    }

    /**
     * @param  string  $crap4j
     *
     * @return CoverageMerger
     */
    public function crap4j($crap4j): self
    {
        $this->crap4j = $crap4j;

        return $this;
    }

    /**
     * @param  string  $html
     *
     * @return CoverageMerger
     */
    public function html($html): self
    {
        $this->html = $html;

        return $this;
    }

    /**
     * @param  string  $php
     *
     * @return CoverageMerger
     */
    public function php($php): self
    {
        $this->php = $php;

        return $this;
    }

    /**
     * @param  string  $text
     *
     * @return CoverageMerger
     */
    public function text($text): self
    {
        $this->text = $text;

        return $this;
    }

    /**
     * Nested creator, creates a FileSet for this task
     *
     * @param  FileSet  $fs  Set of files to copy
     *
     * @return CoverageMerger
     */
    public function fileset(FileSet $fs): self
    {
        $this->filesets[] = $fs;

        return $this;
    }

    /**
     *
     */
    public function merge(): void
    {
        $this->loadPHPUnit();

        $collection = new CoverageCollector();
        foreach ($this->getFilenames() as $file) {
            $this->log("Merging $file");
            $coverage = null;
            $code     = file_get_contents($file);
            $code     = str_replace(CodeCoverage::class, CoverageCollector::class, $code);
            if (!empty($this->pattern)) {
                $code = preg_replace($this->pattern, $this->replace, $code);
            }
            eval('?>' . $code);
            $collection->merge($coverage);
        }
        $this->handleReports($collection->coverage());
    }

    /**
     * Iterate over all filesets and return the filename of all files.
     *
     * @return string[] an array of filenames
     */
    private function getFilenames(): array
    {
        $filenames = [];

        foreach ($this->filesets as $fileset) {
            /** @noinspection SlowArrayOperationsInLoopInspection */
            $filenames = array_merge($filenames, $fileset->getFiles());
        }

        return $filenames;
    }

    /**
     * @param  CodeCoverage  $coverage
     */
    private function handleReports(CodeCoverage $coverage): void
    {
        if ($this->clover) {
            $this->log('Generating code coverage report in Clover XML format ...');

            $writer = new Clover;
            $writer->process($coverage, $this->clover);
        }

        if ($this->crap4j) {
            $this->log('Generating code coverage report in Crap4J XML format...');

            $writer = new Crap4j;
            $writer->process($coverage, $this->crap4j);
        }

        if ($this->html) {
            $this->log('Generating code coverage report in HTML format ...');

            $writer = new Facade;
            $writer->process($coverage, $this->html);
        }

        if ($this->php) {
            $this->log('Generating code coverage report in PHP format ...');

            $writer = new PHP;
            $writer->process($coverage, $this->php);
        }

        if ($this->text) {
            $writer = new Text(50, 90, false, false);
            $writer->process($coverage, $this->text);
        }
    }

    /**
     * @param  null  $pharLocation
     */
    private function loadPHPUnit($pharLocation = null): void
    {
        if (class_exists(CodeCoverage::class)) {
            return;
        }

        if (empty($pharLocation)) {
            $pharLocation = trim(shell_exec('which phpunit'));
        }

        $GLOBALS['_SERVER']['SCRIPT_NAME'] = '-';
        if (file_exists($pharLocation)) {
            ob_start();
            include $pharLocation;
            ob_end_clean();

            include_once 'PHPUnit/Autoload.php';
        }

        if (!class_exists(CodeCoverage::class)) {
            throw new RuntimeException('CoverageMerger requires PHPUnit to be installed');
        }
    }

    /**
     * @param  string  $message
     * @param  string  $level
     */
    private function log(string $message, string $level = 'debug'): void
    {
        echo $message . "\n";
    }
}
