<?php

namespace GreenCape\JoomlaCLI;

use Exception;
use GreenCape\JoomlaCLI\Command\Docker;
use GreenCape\JoomlaCLI\Command\Document\UmlCommand;
use GreenCape\JoomlaCLI\Documentation\API\APIGenerator;
use GreenCape\JoomlaCLI\Repository\VersionList;
use GreenCape\JoomlaCLI\Utility\Array_;
use GreenCape\JoomlaCLI\Utility\Xml;
use GreenCape\Manifest\Manifest;
use League\Flysystem\Adapter\Local;
use League\Flysystem\FileNotFoundException;
use League\Flysystem\Filesystem;
use RuntimeException;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

/**
 * Class FromPhing
 *
 * This class is an intermediate class providing the code used in the phing tasks
 * of GreenCape/build. The code will be distributed to appropriate command classes
 * as development goes on.
 *
 * @since  Class available since Release __DEPLOY_VERSION__
 */
class FromPhing
{
    /**
     * The absolute path to the project root
     *
     * @var string
     */
    private $basedir;

    /**
     * Project settings
     *
     * The settings are read from project.json
     *
     * [name]    => The project name
     * [version] => The project version
     * [paths]   => Array of paths
     *     [source] => Relative path to source files, defaults to `source`
     *
     * @var array
     */
    private $project;

    /**
     * Package settings
     *
     * The settings are read from project.json and combined with data from the manifest file
     *
     * [name]       => The name of the extension, e.g., pkg_example
     * [manifest]   => The path to the manifest file relative to <source>
     * [extensions] => Optional list of extensions for packages, indexed by name (incl. type prefix)
     *     [<name>] => Array (
     *         [name]     => The name of the extension, e.g., com_example
     *         [type]     => Type of extension, e.g., component
     *         [group]    => Plugins only: the plugin group
     *         [manifest] => The path to the manifest file relative to <source>
     *         [archive]  => The path to the extension archive relative to <source>
     *     )
     *
     * @var array
     */
    private $package = [];

    /**
     * Absolute path to source files, defaults to `<basedir>/source`
     *
     * @var string
     */
    private $source;

    /**
     * @var string
     */
    private $tests;
    /**
     * @var string
     */
    private $build;
    /**
     * @var array
     */
    private $dist;
    /**
     * @var string
     */
    private $unitTests;
    /**
     * @var string
     */
    private $integrationTests;
    /**
     * @var string
     */
    private $systemTests;
    /**
     * @var string
     */
    private $testEnvironments;
    /**
     * @var string
     */
    private $buildTemplates;
    /**
     * @var string
     */
    private $serverDockyard;
    /**
     * @var string
     */
    private $bin;
    /**
     * @var string
     */
    private $versionCache;
    /**
     * @var string
     */
    private $downloadCache;
    /**
     * @var array
     */
    private $database;
    /**
     * @var array
     */
    private $environment;
    /**
     * @var callable
     */
    private $filterExpand;
    /**
     * @var Fileset
     */
    private $sourceFiles;
    /**
     * @var Fileset
     */
    private $phpFiles;
    /**
     * @var Fileset
     */
    private $xmlFiles;
    /**
     * @var Fileset
     */
    private $integrationTestFiles;
    /**
     * @var Fileset
     */
    private $distFiles;
    /**
     * @var array
     */
    private $php;
    /**
     * @var OutputInterface
     */
    private $output;
    /**
     * @var string
     */
    private $user;

    /**
     * FromPhing constructor.
     *
     * @param  OutputInterface  $output
     * @param                   $basedir
     * @param                   $projectFile
     */
    public function __construct(OutputInterface $output, $basedir = null, $projectFile = null)
    {
        $this->output = $output;
        $this->init(realpath($basedir ?? '.'), $projectFile ?? 'project.json');
    }

    /**
     * Initialise.
     *
     * @param  string  $dir          The absolute path to the project root
     * @param  string  $projectFile  The path to the project file, relative to $dir
     */
    private function init($dir, $projectFile): void
    {
        $this->basedir = $dir;
        $this->user    = getmyuid() . ':' . getmygid();

        if (!file_exists($this->basedir . '/' . $projectFile)) {
            throw new RuntimeException(
                sprintf(
                    'Project file %s/%s not found',
                    $this->basedir,
                    $projectFile
                )
            );
        }

        $this->echo("Reading project file {$projectFile}", 'debug');

        $settings = json_decode(file_get_contents($this->basedir . '/' . $projectFile), true);

        $this->project = $settings['project'];

        $this->package['name']     = $settings['package']['name'] ?? 'com_' . strtolower(preg_replace('~\W+~', '_',
                $this->project['name']));
        $this->package['type']     = $settings['package']['type'] ?? 'component';
        $this->package['manifest'] = $settings['package']['manifest'] ?? 'manifest.xml';
        $this->package['version']  = $settings['package']['version'] ?? $this->project['version'];

        if (isset($settings['package']['extensions'])) {
            foreach ($settings['package']['extensions'] as $extension) {
                $extension['version']                            = $extension['version'] ?? $this->package['version'];
                $this->package['extensions'][$extension['name']] = $extension;
            }
        }

        $this->echo("Project: {$this->project['name']} {$this->project['version']}", 'verbose');

        $this->source = rtrim($this->basedir . '/' . $this->project['paths']['source'] ?? 'source', '/');

        if (file_exists($this->source . '/' . $settings['package']['manifest'])) {
            $this->echo("Reading manifest file {$settings['package']['manifest']}", 'debug');

            $manifest = Manifest::load($this->source . '/' . $settings['package']['manifest']);

            $this->package['name']   = $manifest->getName();
            $this->package['type']   = $manifest->getType();
            $this->package['target'] = $manifest->getTarget();

            if ($manifest->getType() === 'package') {
                foreach ($manifest->getSection('files')->getStructure() as $extension) {
                    $this->package['extensions'][$extension['@id']]['archive'] = ltrim(($extension['@base'] ?? '') . '/' . $extension['file'],
                        '/');
                    $this->package['extensions'][$extension['@id']]['type']    = $extension['@type'];
                }
            }
        } else {
            $this->echo("Manifest file '{$settings['package']['manifest']}' not found.", 'warning');
        }

        $this->echo(ucfirst($this->package['type']) . " {$this->package['name']} {$this->package['version']}",
            'verbose');

        $this->build            = $this->basedir . '/build';
        $this->tests            = $this->basedir . '/tests';
        $this->bin              = $this->basedir . '/vendor/bin';
        $this->unitTests        = $this->tests . '/unit';
        $this->integrationTests = $this->tests . '/integration';
        $this->systemTests      = $this->tests . '/system';
        $this->testEnvironments = $this->tests . '/servers';
        $this->serverDockyard   = $this->build . '/servers';
        $this->versionCache     = $this->build . '/versions.json';
        $this->downloadCache    = $this->build . '/cache';
        $this->php              = [
            'host' => 'php',
            'port' => 9000,
        ];

        if (empty($this->project['name'])) {
            $this->project['name'] = $this->package['name'];
        }

        $this->dist['basedir'] = "{$this->basedir}/dist/{$this->package['name']}-{$this->project['version']}";

        $this->mkdir($this->downloadCache);

        $this->filterExpand = function ($content) {
            return $this->expand($content);
        };

        $this->sourceFiles          = (new Fileset($this->source))->include('**.*');
        $this->phpFiles             = (new Fileset($this->source))->include('**.*.php');
        $this->xmlFiles             = (new Fileset($this->source))->include('**.*.xml');
        $this->integrationTestFiles = (new Fileset($this->integrationTests))->include('**.*');
        $this->distFiles            = (new Fileset($this->dist['basedir']))->include('**.*');

        $this->buildTemplates = dirname(__DIR__) . '/build';
    }

    /**
     * @param  string  $message
     * @param  string  $level
     */
    private function echo(string $message, string $level): void
    {
        $verbosity = [
            'info'    => OutputInterface::VERBOSITY_NORMAL,
            'warning' => OutputInterface::VERBOSITY_NORMAL,
            'error'   => OutputInterface::VERBOSITY_NORMAL,
            'verbose' => OutputInterface::VERBOSITY_VERBOSE,
            'debug'   => OutputInterface::VERBOSITY_DEBUG,
        ];

        $this->output->writeln(strtoupper($level) . ': ' . str_replace($this->basedir, '.', $message),
            $verbosity[$level]);
    }

    /**
     * @param  string  $dir
     */
    private function mkdir(string $dir): void
    {
        $this->exec("mkdir --mode=0775 --parents $dir", $this->basedir);
    }

    /**
     * @param  string  $content
     *
     * @return string
     */
    private function expand(string $content): string
    {
        return preg_replace_callback(
            '~\${(.*?)}~',
            function ($match) {
                $var   = lcfirst(str_replace(' ', '', ucwords(str_replace('-', ' ', $match[1]))));
                $parts = explode('.', $var);
                $var   = array_shift($parts);
                $var   = $this->{$var};

                for ($index = array_shift($parts); $index !== null; $index = array_shift($parts)) {
                    $var = $var[$index];
                }

                return $var;
            },
            $content
        );
    }

    /** @noinspection PhpUnused */

    /**
     * @param  string  $command
     * @param  string  $dir
     * @param  bool    $passthru
     *
     * @return string|null
     */
    private function exec(string $command, string $dir = '.', bool $passthru = true): ?string
    {
        $this->echo("Running `{$command}` in `{$dir}`", 'debug');

        if ($dir !== '.') {
            $current = getcwd();
            $command = 'cd ' . $dir . ' && ' . $command . ' && cd ' . $current;
        }

        $result = '';

        if ($passthru) {
            passthru($command . ' 2>&1', $result);
        } else {
            $result = shell_exec($command . ' 2>&1');
        }

        return $result;
    }

    /************************
     * Docker related tasks *
     ************************/

    /**
     * Updates the build environment
     */
    public function selfUpdate(): void
    {
        $this->clean();
        $this->exec('git pull origin && composer update', $this->build);
    }

    /**
     * Cleanup artifact directories
     */
    private function clean(): void
    {
        $this->delete("{$this->build}/logs");
        $this->delete("{$this->build}/servers");
    }

    /**
     * @param  Fileset|string  $fileset
     */
    private function delete($fileset): void
    {
        if (is_string($fileset)) {
            $this->deleteFile($fileset);

            return;
        }

        foreach ($fileset->getFiles() as $file) {
            $this->deleteFile($file);
        }
    }

    /**
     * @param $file
     */
    private function deleteFile($file): void
    {
        if (!file_exists($file)) {
            return;
        }

        $this->exec(is_dir($file) ? "rm -rf $file" : "rm $file");
    }

    /**
     * Starts the test containers after rebuilding them.
     */
    public function dockerUp(): void
    {
        if (!file_exists("{$this->serverDockyard}/docker-compose.yml")) {
            throw new RuntimeException('Servers are not set up.');
        }

        $this->exec(
            'docker-compose up -d',
            $this->serverDockyard
        );

        // Give the containers time to setup
        sleep(15);
    }

    /**
     * Removes the content of test containers.
     */
    public function dockerRemove(): void
    {
        if (!file_exists("{$this->serverDockyard}/docker-compose.yml")) {
            $this->echo('Servers are not set up. Nothing to do', 'info');

            return;
        }

        $this->exec(
            'docker-compose rm --force',
            $this->serverDockyard
        );
        $this->delete($this->serverDockyard);
    }

    /*******************************
     * Documentation related tasks *
     *******************************/

    /**
     * Generate API documentation using PHPDocumentor2
     *
     * @param $apidocTitle
     *
     * @noinspection PhpUnusedPrivateMethodInspection
     */
    private function documentPhpdoc($apidocTitle): void
    {
        $this->exec(
            "{$this->bin}/phpdoc --target={$this->build}/report/api --directory={$this->source} --title=\"{$apidocTitle}\" --template=responsive",
            $this->basedir
        );
        $this->copy(
            (new Fileset("{$this->build}/plantuml"))
                ->include('*.js'),
            "{$this->build}/report/api/js",
            static function ($content) {
                return str_replace("'rawdeflate.js'", "'../js/rawdeflate.js'", $content);
            }
        );
        $this->reflexive(
            (new Fileset("{$this->build}/report/api/classes"))
                ->include('*.html'),
            static function ($content) {
                $content = str_replace('</head>',
                    '<script type="text/javascript" src="../js/jquery_plantuml.js"></script></head>', $content);
                $content = preg_replace(
                    "~<th>startuml</th>(\n)<td>(.+?)</td>~sm",
                    /** @lang text */ '<th>UML</th><td><img uml="\\1!include ' . $this->build . '/report/api/uml/skin.puml\\1\\2\\1" alt=""/></td>',
                    $content
                );
                $content = preg_replace("~<tr>\s*<th>enduml</th>\s*<td></td>\s*</tr>~m", '', $content);

                return $content;
            }
        );
    }

    /**
     * @param  Fileset|string  $fileset
     * @param  string          $toDir
     * @param  callable|null   $filter
     */
    private function copy($fileset, string $toDir, callable $filter = null): void
    {
        if (is_string($fileset)) {
            $this->copyFile($fileset, $toDir, $filter);

            return;
        }

        foreach ($fileset->getFiles() as $file) {
            $this->copyFile($file, str_replace($fileset->getDir(), $toDir, $file), $filter);
        }
    }

    /**
     * @param  Fileset|string  $fileset
     * @param  callable        $filter
     */
    private function reflexive($fileset, callable $filter): void
    {
        if (is_string($fileset)) {
            $this->copyFile($fileset, $fileset, $filter);

            return;
        }

        foreach ($fileset->getFiles() as $file) {
            $this->copyFile($file, $file, $filter);
        }
    }

    /**
     * @param  string         $file
     * @param  string         $toFile
     * @param  callable|null  $filter
     */
    private function copyFile(string $file, string $toFile, callable $filter = null): void
    {
        if (is_dir($file)) {
            return;
        }

        $this->echo("Copying {$file}" . ($filter !== null ? ' with filter' : '') . " to {$toFile}", 'debug');

        $content = file_get_contents($file);

        if (is_callable($filter)) {
            $content = $filter($content);
        }

        file_put_contents($toFile, $content);
    }

    /**
     * Creates a patch set ready to drop into an existing installation.
     */
    public function patchCreate(): void
    {
        $patchsetLocation = "dist/{$this->package['name']}-{$this->project['version']}-full";
        $uptodate         = $this->isUptodate(
            new Fileset($patchsetLocation),
            new Fileset($this->source)
        );

        if ($uptodate) {
            $this->echo("Patchset {$patchsetLocation} is up to date", 'info');

            return;
        }

        $this->delete($patchsetLocation);
        $this->mkdir($patchsetLocation);
        $this->copy(
            (new Fileset($this->source))
                ->exclude('installation/**'),
            $patchsetLocation
        );

        if ($this->package['type'] === 'com_') {
            $this->copy(
                (new Fileset($this->source))
                    ->include('installation/**'),
                "{$patchsetLocation}/administrator/components/com_{$this->package['name']}"
            );
        }
    }

    /*********************************
     * Quality Metrics related tasks *
     *********************************/

    /**
     * Checks if (every file from) target is newer than (every file from) source
     *
     * @param  Fileset|string  $target
     * @param  Fileset|string  ...$sources
     *
     * @return bool
     */
    private function isUptodate($target, ...$sources): bool
    {
        $targetFiles = is_string($target) ? [$target] : $target->getFiles();

        $targetTime = array_reduce(
            $targetFiles,
            static function ($carry, $file) {
                return min($carry, filemtime($file));
            }
        );

        foreach ($sources as $source) {
            $sourceFiles = is_string($source) ? [$source] : $source->getFiles();
            foreach ($sourceFiles as $file) {
                if (filemtime($file) > $targetTime) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     *
     */
    public function testTargets(): void
    {
        $docker = new Docker($this->serverDockyard);
        $this->echo('Matching containers: ' . implode(', ', $docker->dockerList()), 'info');
        $this->echo('Defined containers: ' . implode(', ', $docker->dockerDef()), 'info');
    }

    /**
     * Generate the distribution
     *
     * @throws FileNotFoundException
     */
    public function dist(): void
    {
        $this->build();
        $this->distPrepare();

        $packageName = "{$this->package['name']}-{$this->project['version']}";
        $this->exec("zip -r ../packages/{$packageName}.zip * > /dev/null", $this->dist['basedir']);
        $this->exec("tar --create --gzip --file ../packages/{$packageName}.tar.gz * > /dev/null",
            $this->dist['basedir']);
        $this->exec("tar --create --bzip2 --file ../packages/{$packageName}.tar.bz2 * > /dev/null",
            $this->dist['basedir']);
    }

    /**
     * Performs all tests and generates documentation and the quality report.
     *
     * @throws FileNotFoundException
     * @throws Exception
     */
    public function build(): void
    {
        $this->prepare();
        $this->test();
        $this->quality();
        $this->document();
    }

    /**
     * Create distribution directory
     */
    public function distPrepare(): void
    {
        $this->phpAb();
        $this->distClean();

        // Installation files
        $this->mkdir($this->dist['basedir']);
        $this->copy(
            (new Fileset("{$this->source}/installation"))
                ->include('*.php')
                ->include('*.xml'),
            $this->dist['basedir']
        );
        $this->copy(
            (new Fileset($this->basedir))
                ->include('*.md'),
            $this->dist['basedir']
        );

        // Admin component
        $this->mkdir("{$this->dist['basedir']}/{$this->package['administration']['files']['folder']}");
        $this->copy(
            (new Fileset("{$this->source}/administrator/components/{$this->package['name']}"))
                ->include($this->package['administration']['files']['folder'])
                ->include($this->package['administration']['files']['filename']),
            "{$this->dist['basedir']}/{$this->package['administration']['files']['folder']}"
        );

        // Admin language
        $this->mkdir("{$this->dist['basedir']}/{$this->package['administration']['languages']['folder']}");
        $this->copy(
            new Fileset("{$this->source}/administrator/language"),
            "{$this->dist['basedir']}/{$this->package['administration']['languages']['folder']}"
        );

        // Frontend component
        $this->mkdir("{$this->dist['basedir']}/{$this->package['files']['folder']}");
        $this->copy(
            (new Fileset("{$this->source}/components/{$this->package['name']}"))
                ->include($this->package['files']['folder'])
                ->include($this->package['files']['filename']),
            "{$this->dist['basedir']}/{$this->package['files']['folder']}"
        );

        // Frontend language
        $this->mkdir("{$this->dist['basedir']}/{$this->package['languages']['folder']}");
        $this->copy(
            new Fileset("{$this->source}/language"),
            "{$this->dist['basedir']}/{$this->package['languages']['folder']}"
        );
    }

    /**
     * Create artifact directories
     */
    private function prepare(): void
    {
        $this->mkdir("{$this->build}/logs");
    }

    /***************************
     * Patch Set related tasks *
     ***************************/

    /**
     * Runs all tests locally and in the test containers.
     *
     * @throws FileNotFoundException
     */
    public function test(): void
    {
        $this->testUnit();
        $this->testIntegration();
        $this->testSystem();
        $this->testCoverageReport();
    }

    /**********************
     * Test related tasks *
     **********************/

    /**
     * Generates a quality report using CodeBrowser.
     */
    public function quality(): void
    {
        $this->qualityDepend();
        $this->qualityMessDetect();
        $this->qualityCopyPasteDetect();
        $this->qualityCheckStyle();
        $this->qualityCodeBrowser();
    }

    /**
     * Generates API documentation using the specified generator.
     *
     * @param $apidocGenerator
     *
     * @throws Exception
     */
    public function document($apidocGenerator = null): void
    {
        $apidocGenerator = $apidocGenerator ?? 'apigen'; // Supported generators: phpdoc, apigen;
        $this->documentClean();
        $this->documentUml();
        $this->documentChangelog();

        $generator = new APIGenerator($apidocGenerator ?? 'apigen');
        $generator->run("{$this->project['name']} {$this->project['version']} API Documentation", $this->source,
            $this->build . '/report/api', '../uml');
    }

    /**
     * Generate autoload script
     */
    private function phpAb(): void
    {
        $this->exec("{$this->bin}/phpab --tolerant --basedir . --output {$this->tests}/autoload.php {$this->tests}");
        $this->exec("{$this->bin}/phpab --tolerant --basedir {$this->source} --output {$this->source}/autoload.php {$this->source}");
    }

    /**
     * Cleanup distribution directory
     */
    public function distClean(): void
    {
        $this->delete($this->dist['basedir']);
    }

    /**
     * Runs local unit tests
     *
     * @throws FileNotFoundException
     */
    public function testUnit(): void
    {
        $this->ensureBootstrapExistsForUnitTests();
        $this->setupLocalJoomla("{$this->basedir}/joomla");

        $this->phpAb();
        $this->mkdir("{$this->build}/logs/coverage");
        $command = "{$this->bin}/phpunit"
                   . " --bootstrap {$this->tests}/unit/bootstrap.php"
                   . " --coverage-php {$this->build}/logs/coverage/unit.cov"
                   . " --whitelist {$this->source}"
                   . ' --colors=always'
                   . " {$this->unitTests}";
        $this->exec($command, $this->basedir);
        $this->reflexive(
            new Fileset("{$this->build}/logs/coverage"),
            static function ($content) {
                return preg_replace("~'(.*?)Test::test~", "'Unit: \1Test::test", $content);
            }
        );
    }

    /**
     * Runs integration tests on all test installations.
     *
     * @throws FileNotFoundException
     */
    public function testIntegration(): void
    {
        $this->dockerStart();

        $environments = (new Fileset($this->testEnvironments))
            ->include('*.xml')
            ->exclude('database.xml')
            ->exclude('default.xml')
            ->getFiles()
        ;

        foreach ($environments as $environmentDefinition) {
            $this->testIntegrationSingle($environmentDefinition);
        }

        $this->dockerStop();
    }

    /**
     * Runs system tests on all test installations.
     *
     * @throws FileNotFoundException
     */
    public function testSystem(): void
    {
        $this->dockerStart();

        $this->delete("{$this->build}/screenshots");
        $this->mkdir("{$this->build}/screenshots");

        $environments = (new Fileset($this->testEnvironments))
            ->include('*.xml')
            ->exclude('database.xml')
            ->exclude('default.xml')
            ->getFiles()
        ;

        foreach ($environments as $environmentDefinition) {
            $this->testSystemSingle($environmentDefinition);
        }

        $this->dockerStop();
    }

    /**
     * Creates an consolidated HTML coverage report
     */
    public function testCoverageReport(): void
    {
        $this->mkdir("{$this->build}/report/coverage");

        $merger = new CoverageMerger();
        $merger
            ->fileset((new Fileset("{$this->build}/logs/coverage"))->include('**/*.cov'))
            ->html("{$this->build}/report/coverage")
            ->clover("{$this->build}/logs/clover.xml")
            ->merge()
        ;

        $this->reflexive(
            new Fileset("{$this->build}/report/coverage"),
            function ($content) {
                return str_replace($this->source, $this->project['name'], $content);
            }
        );
    }

    /**
     * Generates depend.xml and software metrics charts using PHP Depend.
     */
    public function qualityDepend(): void
    {
        $this->mkdir("{$this->build}/logs/charts");
        $command = "{$this->bin}/pdepend"
                   . ' --suffix=php'
                   . " --jdepend-chart={$this->build}/logs/charts/dependencies.svg"
                   . " --jdepend-xml={$this->build}/logs/depend.xml"
                   . " --overview-pyramid={$this->build}/logs/charts/overview-pyramid.svg"
                   . " --summary-xml={$this->build}/logs/summary.xml"
                   . " {$this->source}";

        $this->exec($command);
    }

    /******************************
     * Distribution related tasks *
     ******************************/

    /**
     * Generates pmd.xml using PHP MessDetector.
     */
    public function qualityMessDetect(): void
    {
        $command = "{$this->bin}/phpmd"
                   . " {$this->source}"
                   . ' xml'
                   . " {$this->buildTemplates}/config/phpmd.xml"
                   . ' --suffixes php'
                   . " --reportfile {$this->build}/logs/pmd.xml";

        $this->exec($command);
    }

    /**
     * Generates pmd-cpd.xml using PHP CopyPasteDetector.
     */
    public function qualityCopyPasteDetect(): void
    {
        $command = 'phpcpd'
                   . " --log-pmd={$this->build}/logs/pmd-cpd.xml"
                   . ' --fuzzy'
                   . " {$this->source}";

        $this->exec($command);
    }

    /**
     * Generates checkstyle.xml using PHP CodeSniffer.
     */
    public function qualityCheckStyle(): void
    {
        $command = 'phpcs'
                   . ' -s'
                   . ' --report=checkstyle'
                   . " --report-file={$this->build}/logs/checkstyle.xml"
                   . " --standard={$this->basedir}/vendor/greencape/coding-standards/src/Joomla"
                   . " {$this->source}";

        $this->exec($command);
    }

    /******************
     * Internal tasks *
     ******************/

    /**
     * Aggregates the results from all the measurement tools.
     */
    public function qualityCodeBrowser(): void
    {
        $this->mkdir("{$this->build}/report/code-browser");

        // CodeBrowser has a bug regarding crapThreshold, so remove all crap-values below 10 (i.e., 1 digit)
        $this->reflexive(
            (new Fileset("{$this->build}/logs"))
                ->include('clover.xml'),
            static function ($content) {
                return preg_replace('~crap="\d"~', '', $content);
            }
        );

        $command = "{$this->bin}/phpcb"
                   . " --log={$this->build}/logs"
                   . " --output={$this->build}/report/code-browser"
                   . ' --crapThreshold=10';

        $this->exec($command, $this->basedir);
    }

    /**
     *
     */
    public function documentClean(): void
    {
        $this->delete("{$this->build}/report/api");
        $this->mkdir("{$this->build}/report/api");
    }

    /**
     * @param  bool  $keepSources
     *
     * @throws Exception
     */
    public function documentUml(bool $keepSources = false): void
    {
        $basepath = $this->source;
        $skin     = 'bw-gradient';
        $target   = "{$this->build}/report/uml";
        $svg      = $keepSources ? '--no-svg' : '';

        $input = new StringInput("--basepath={$basepath} --skin={$skin} --output={$target} {$svg}");

        $command = new UmlCommand();
        $command->run($input, $this->output);
    }

    /**
     * Generates CHANGELOG.md from the git commit history.
     */
    public function documentChangelog(): void
    {
        $this->exec("git log --pretty=format:'%+d %ad [%h] %s (%an)' --date=short > {$this->basedir}/CHANGELOG.md");
        $this->reflexive(
            (new Fileset($this->basedir))
                ->include('CHANGELOG.md'),
            function ($content) {
                $content = preg_replace("~\n\s*\(([^)]+)\)~", "\n\n## Version $1\n\n", $content);
                $content = preg_replace("~\n +~", "\n", $content);
                $content = preg_replace("~\n(\d)~", "\n    $1", $content);
                $content = preg_replace("~^\n~", "# {$this->project['name']} Changelog\n", $content);

                return $content;
            }
        );
    }

    private function ensureBootstrapExistsForUnitTests(): void
    {
        if (!file_exists("{$this->tests}/unit/bootstrap.php")) {
            // Find bootstrap file
            $bootstrap = $this->versionMatch(
                'bootstrap-(.*).php',
                "{$this->buildTemplates}/template/tests/unit",
                $this->package['target']
            );

            if (empty($bootstrap)) {
                throw new RuntimeException("No bootstrap file found for Joomla! {$this->package['target']}");
            }

            $this->copy($bootstrap, "{$this->tests}/unit/bootstrap.php");
        }

        if (!file_exists("{$this->tests}/unit/autoload.php")) {
            $this->copy("{$this->buildTemplates}/template/tests/unit/autoload.php", "{$this->tests}/unit/autoload.php");
        }
    }

    /**
     * @param  string  $target
     *
     * @throws FileNotFoundException
     */
    private function setupLocalJoomla(string $target): void
    {
        if (file_exists("{$this->basedir}/joomla/index.php")) {
            return;
        }

        $this->mkdir($target);

        $tarball = $this->joomlaDownload($this->package['target'], $this->versionCache, $this->downloadCache);
        $this->untar($target, $tarball);
        $version = preg_replace('~^.*?(\d+\.\d+\.\d+)\.tar\.gz$~', '\1', $tarball);

        $autoload = $this->versionMatch('autoload-(.*?).php', "{$this->buildTemplates}/joomla", $version);
        $classmap = $this->versionMatch('classmap-(.*?).php', "{$this->buildTemplates}/joomla", $version);

        $this->copy($autoload, "{$target}/autoload.php");
        $this->copy($classmap, "{$target}/classmap.php");
    }

    /**
     * Starts the test containers, building them only if not existing.
     *
     * @throws FileNotFoundException
     */
    public function dockerStart(): void
    {
        $this->dockerBuild();
        $this->exec(
            'docker-compose up --no-recreate -d',
            $this->serverDockyard
        );

        // Give the containers time to setup
        sleep(15);
    }

    /**
     * Run integrations tests on a single test installation
     *
     * @param $environmentDefinition
     */
    private function testIntegrationSingle($environmentDefinition): void
    {
        $target = basename($environmentDefinition, '.xml');

        // Get the environment settings
        $environment = Array_::merge(
            Array_::merge(
                [
                    'name'   => $target,
                    'server' => [
                        'type' => 'nginx',
                        'tld'  => 'dev',
                    ],
                ],
                Xml::xmlProperty("{$this->testEnvironments}/default.xml", false, true)
            ),
            Xml::xmlProperty("{$this->testEnvironments}/{$environmentDefinition}", false, true)
        );
        $domain      = "{$environment['name']}.{$this->environment['server']['tld']}";
        $cmsRoot     = "{$this->serverDockyard}/{$environment['server']['type']}/html/{$domain}";

        $container = $environment['server']['type'] === 'nginx' ? 'servers_php_1' : "servers_{$environment['server']['type']}_1";

        $uptodate = $this->isUptodate("{$this->build}/logs/coverage/integration-{$target}.cov",
            $this->sourceFiles,
            $this->integrationTestFiles,
            (new Fileset($this->testEnvironments))
                ->include($environmentDefinition)
        );

        if ($uptodate) {
            $this->echo("Integration test for {$target} is up to date - skipping.", 'info');

            return;
        }

        $this->echo("Integration test on {$target}", 'info');
        $this->delete("{$cmsRoot}/build/logs");
        $this->mkdir("{$cmsRoot}/build/logs");

        $applications = (new Fileset("{$cmsRoot}/tests/integration"))->include('*',
            Fileset::NO_RECURSE | Fileset::ONLY_DIRS);

        foreach ($applications as $application) {
            $this->testIntegrationApp($application, $domain, $cmsRoot, $target, $container);
        }

        $merger = new CoverageMerger();
        $merger
            ->fileset((new Fileset("{$cmsRoot}/build/logs"))->include('**/*.cov'))
            ->pattern("/var/www/html/{$domain}")
            ->replace("{$this->source}/")
            ->php("{$cmsRoot}/build/logs/integration-{$target}.cov")
            ->merge()
        ;

        $this->copy(
            new Fileset("{$cmsRoot}/build/logs/integration-{$target}.cov"),
            "{$this->build}/logs/coverage",
            static function ($content) use ($target) {
                return preg_replace("~'(.*?)Test::test~", "'{$target}: \1Test::test", $content);
            }
        );
    }

    /**
     * Stops and removes the test containers.
     */
    public function dockerStop(): void
    {
        if (!file_exists("{$this->serverDockyard}/docker-compose.yml")) {
            $this->echo('Servers are not set up. Nothing to do', 'info');

            return;
        }

        $this->exec(
            'docker-compose stop',
            $this->serverDockyard
        );

        // Give the containers time to stop
        sleep(2);
    }

    /**
     * Runs system tests on a single test installation.
     *
     * @param $environmentDefinition
     */
    private function testSystemSingle($environmentDefinition): void
    {
        $target = basename($environmentDefinition, '.xml');

        // Get the environment settings
        $environment = Array_::merge(
            Array_::merge(
                [
                    'name'    => $target,
                    'server'  => [
                        'type' => 'nginx',
                        'tld'  => 'dev',
                    ],
                    'browser' => [
                        'type' => 'firefox',
                    ],
                ],
                Xml::xmlProperty("{$this->testEnvironments}/default.xml", false, true)
            ),
            Xml::xmlProperty("{$this->testEnvironments}/{$environmentDefinition}", false, true)
        );
        $domain      = "{$environment['name']}.{$this->environment['server']['tld']}";
        $cmsRoot     = "{$this->serverDockyard}/{$environment['server']['type']}/html/{$domain}";

        $systemTestFilter = static function ($content) use ($environment, $domain, $target) {
            return str_replace(
                [
                    '@CMS_ROOT@',
                    '@TARGET@',
                    '@DOMAIN@',
                    '@BROWSER@',
                ],
                [
                    "/var/www/html/{$domain}",
                    $target,
                    $domain,
                    $environment['browser']['type'],
                ],
                $content
            );
        };

        $this->echo("System test for {$target} on {$domain}", 'info');

        // Find bootstrap file
        $bootstrap = $this->versionMatch(
            'bootstrap-(.*).php',
            "{$this->buildTemplates}/template/tests/system",
            $this->environment['joomla']['version']
        );

        if (empty($bootstrap)) {
            throw new RuntimeException("No bootstrap file found for Joomla! {$this->environment['joomla']['version']}");
        }

        // Configure bootstrap file
        $this->copy(
            $bootstrap,
            "{$cmsRoot}/tests/system/bootstrap.php",
            $systemTestFilter
        );

        // Configure phpunit
        $this->copy(
            "{$this->buildTemplates}/template/tests/system/phpunit.xml",
            "{$cmsRoot}/tests/system/phpunit.xml",
            $systemTestFilter
        );

        $container = "servers_{$environment['server']['type']}_1";

        $this->exec("docker exec --user={$this->user} {$container} /bin/bash -c \"cd /var/www/html/{$domain}/tests/system; /usr/local/lib/php/vendor/bin/phpunit\"");

        $this->copy(
            (new Fileset("{$cmsRoot}/build/logs"))
                ->include('system-*.cov'),
            "{$this->build}/logs/coverage",
            function ($content) use ($target, $domain) {
                $content = str_replace("/var/www/html/{$domain}", $this->source, $content);
                $content = preg_replace("~'(.*?)Test::test~", "{$target}: \1Test::test", $content);

                return $content;
            }
        );
    }

    /**
     * @param $pattern
     * @param $path
     * @param $version
     *
     * @return string|null
     */
    private function versionMatch($pattern, $path, $version): ?string
    {
        $bestVersion = '0';
        $bestFile    = null;
        foreach (glob("$path/*") as $filename) {
            if (preg_match("~{$pattern}~", $filename, $match) && version_compare($bestVersion, $match[1],
                    '<') && version_compare($match[1], $version, '<=')) {
                $bestVersion = $match[1];
                $bestFile    = $filename;
            }
        }

        return $bestFile;
    }

    /**
     * @param          $version
     * @param  string  $versionCache
     * @param  string  $downloadCache
     *
     * @return string
     * @throws FileNotFoundException
     */
    private function joomlaDownload($version, string $versionCache, string $downloadCache): string
    {
        $versions  = $this->joomlaVersions($versionCache);
        $requested = $version;
        $version   = $versions->resolve($version);
        $tarball   = $downloadCache . '/' . $version . '.tar.gz';

        if (!$versions->isBranch($version) && file_exists($tarball)) {
            return $tarball;
        }

        if ($versions->isBranch($version)) {
            $url = 'http://github.com/joomla/joomla-cms/tarball/' . $version;

            return $this->download($tarball, $url);
        }

        if ($versions->isTag($version)) {
            try // to get the official release for that version
            {
                $url = "https://github.com/joomla/joomla-cms/releases/download/{$version}/Joomla_{$version}-Stable-Full_Package.tar.gz";

                return $this->download($tarball, $url);
            } catch (Throwable $exception) // else get it from the archive
            {
                $repository = $versions->getRepository($version);
                $url        = 'https://github.com/' . $repository . '/archive/' . $version . '.tar.gz';

                return $this->download($tarball, $url);
            }
        }

        throw new RuntimeException("$requested: Version is unknown");
    }

    /**
     * @param  string  $toDir
     * @param  string  $file
     */
    private function untar(string $toDir, string $file): void
    {
        $this->mkdir($toDir);
        $this->exec("tar -zxvf {$file} -C {$toDir} --exclude-vcs");

        // If $toDir contains only a single directory, we need to lift everything up one level.
        $dirList = glob("{$toDir}/*", GLOB_ONLYDIR);

        if (count($dirList) === 1) {
            $this->copy(
                new Fileset($dirList[0]),
                $toDir
            );

            $this->delete($dirList[0]);
        }
    }

    /**
     * Generates the contents and prepares the test containers.
     *
     * @throws FileNotFoundException
     */
    public function dockerBuild(): void
    {
        $testEnvironments = $this->testEnvironments;

        if (!file_exists($this->serverDockyard . '/docker-compose.yml')) {
            $uptodate = false;
        } else {
            $uptodate = $this->isUptodate(
                $this->serverDockyard . '/docker-compose.yml',
                (new Fileset('.'))
                    ->include($this->source . '/**')
                    ->include($this->integrationTests . '/**')
                    ->include($testEnvironments . '/**')
            );
        }

        if ($uptodate) {
            $this->echo('Container setups are up to date - skipping.', 'info');

            return;
        }

        // Recreate directories for container contents
        $this->delete($this->serverDockyard);
        $this->mkdir($this->serverDockyard . '/nginx/conf');
        $this->mkdir($this->serverDockyard . '/nginx/html');
        $this->mkdir($this->serverDockyard . '/apache/conf');
        $this->mkdir($this->serverDockyard . '/apache/html');
        $this->mkdir($this->serverDockyard . '/proxy/conf');
        $this->mkdir($this->serverDockyard . '/mysql');
        $this->mkdir($this->serverDockyard . '/postgresql');

        // Get available Joomla! versions
        $this->joomlaVersions($this->versionCache);

        $this->database = (new Settings($this->project['name']))->defaultDatabase($testEnvironments . '/database.xml');

        $this->copy(
            (new Fileset($this->buildTemplates . '/docker'))
                ->include('docker-compose.yml'),
            $this->serverDockyard,
            $this->filterExpand
        );

        // Handle each test environment
        foreach (glob($testEnvironments . '/*.xml') as $environmentDefinition) {
            if (in_array(basename($environmentDefinition), ['database.xml', 'default.xml'])) {
                continue;
            }

            $this->dockerBuildSystem($environmentDefinition);
        }
    }

    /**
     * @param $application
     * @param $domain
     * @param $cmsRoot
     * @param $target
     * @param $container
     */
    private function testIntegrationApp($application, $domain, $cmsRoot, $target, $container): void
    {
        if (empty($application)) {
            return;
        }

        $integrationTestFilter = static function ($content) use ($application, $domain, $target) {
            return str_replace(
                [
                    '@APPLICATION@',
                    '@CMS_ROOT@',
                    '@TARGET@',
                ],
                [
                    $application,
                    "/var/www/html/{$domain}",
                    $target,
                ],
                $content
            );
        };

        $this->echo($application, 'info');

        // Find bootstrap file
        $bootstrap = $this->versionMatch(
            'bootstrap-(.*).php',
            "{$this->buildTemplates}/template/tests/integration",
            $this->environment['joomla']['version']
        );

        if (empty($bootstrap)) {
            throw new RuntimeException("No bootstrap file found for Joomla! {$this->environment['joomla']['version']}");
        }

        // Configure bootstrap file
        $this->copy(
            $bootstrap,
            "{$cmsRoot}/tests/integration/{$application}/bootstrap.php",
            $integrationTestFilter
        );

        // Configure phpunit
        $this->copy(
            "{$this->buildTemplates}/template/tests/integration/phpunit.xml",
            "{$cmsRoot}/tests/integration/{$application}/phpunit.xml",
            $integrationTestFilter
        );

        $this->exec("docker exec --user={$this->user} {$container} /bin/bash -c \"cd /var/www/html/{$domain}/tests/integration/{$application}; /usr/local/lib/php/vendor/bin/phpunit\"");
    }

    /**
     * @param $versionCache
     *
     * @return VersionList
     * @throws FileNotFoundException
     */
    private function joomlaVersions($versionCache): VersionList
    {
        return new VersionList(new Filesystem(new Local(dirname($versionCache))), basename($versionCache));
    }

    /**
     * @param  string  $filename
     * @param  string  $url
     *
     * @return string
     */
    private function download(string $filename, string $url): string
    {
        $bytes = file_put_contents($filename, @fopen($url, 'rb'));

        if ($bytes === false || $bytes === 0) {
            throw new RuntimeException("Failed to download $url");
        }

        return $filename;
    }

    /**
     * @param  string  $xmlFile  XML file containing the environment definition
     *
     * @throws FileNotFoundException
     */
    private function dockerBuildSystem(string $xmlFile): void
    {
        $environment = (new Settings($this->project['name']))->environment($xmlFile,
            $this->testEnvironments . '/default.xml');

        $domain = "{$environment['name']}.{$environment['server']['tld']}";

        // Download and unpack the specified Joomla! version
        $cmsRoot = $this->serverDockyard . '/' . $environment['server']['type'] . '/html/' . $domain;
        $tarball = $this->joomlaDownload($environment['joomla']['version'], $this->versionCache,
            $this->downloadCache);
        $this->untar($cmsRoot, $tarball);
        $version = preg_replace('~^.*?(\d+\.\d+\.\d+)\.tar\.gz$~', '\1', $tarball);

        // Add SUT
        $this->copy(
            (new Fileset($this->source))
                ->exclude('installation/**/*'),
            $cmsRoot
        );

        // Add test files
        $this->delete($cmsRoot . '/tests');
        $this->mkdir($cmsRoot . '/tests');
        $this->copy(
            (new Fileset($this->tests))
                ->include('mocks/**/*')
                ->include('integration/**/*')
                ->include('system/**/*')
                ->include('autoload.php'),
            $cmsRoot . '/tests',
            $this->filterExpand
        );
        $this->copy(
            (new Fileset($this->buildTemplates . '/template/selenium'))
                ->exclude('server_files/**/*'),
            $cmsRoot . '/tests/system'
        );

        $this->exec(
            "phpab --tolerant --basedir '.' --exclude '*Test.php' --template '{$this->buildTemplates}/template/tests/system/autoload.php.in' --output '{$cmsRoot}/tests/system/autoload.php' .",
            "{$cmsRoot}/tests/system"
        );

        // Create build directory
        $this->delete($cmsRoot . '/build');
        $this->mkdir($cmsRoot . '/build/logs/coverage');

        // Build the database import script
        if (!file_exists("{$cmsRoot}/installation/sql/{$environment['database']['engine']}")) {
            throw new RuntimeException("Joomla! {$version} does not support {$environment['database']['engine']} databases");
        }

        // Gather the database contents
        $coreData   = "{$cmsRoot}/installation/sql/{$environment['database']['engine']}/joomla.sql";
        $sampleData = "{$cmsRoot}/installation/sql/{$environment['database']['engine']}/sample_{$environment['joomla']['sampledata']}.sql";

        if (!file_exists($sampleData)) {
            throw new RuntimeException("No '{$environment['joomla']['sampledata']}' sample data found for Joomla! {$version} with {$environment['database']['engine']} database");
        }

        $testData = $this->versionMatch(
            'joomla-(.*).sql',
            "{$this->buildTemplates}/template/{$environment['database']['engine']}",
            $version
        );

        if (empty($testData)) {
            throw new RuntimeException("No test data found for Joomla! {$version} with {$environment['database']['engine']} database");
        }

        $this->echo(<<<ECHO
    Joomla version:  {$version}
    Domain:          {$domain}
    Server:          {$environment['server']['type']}

    Database type:   {$environment['database']['engine']}:{$database[$environment['database']['engine']]['version']}
                     ({$environment['database']['driver']})
    Database name:   {$environment['database']['name']}
    Database prefix: {$environment['database']['prefix']}
    Database user:   {$database[$environment['database']['engine']]['user']}:{$database[$environment['database']['engine']]['password']}
ECHO
            ,
            'info'
        );

        // Build the import files
        $importSql = "{$this->serverDockyard}/{$environment['database']['engine']}/{$environment['name']}.sql";
        $importSh  = "{$this->serverDockyard}/{$environment['database']['engine']}/{$environment['name']}.sh";

        if ($environment['database']['name'] === $database[$environment['database']['engine']]['name']) {
            file_put_contents($importSql, '');
        } else {
            $this->copy(
                "{$this->buildTemplates}/template/{$environment['database']['engine']}/createdb.sql",
                $importSql,
                $this->filterExpand
            );
        }

        $this->exec("cat '{$coreData}' >> '{$importSql}'");
        $this->exec("cat '{$sampleData}' >> '{$importSql}'");
        $this->exec("cat '{$testData}' >> '{$importSql}'");
        $this->exec("sed -i 's/#__/{$environment['database']['prefix']}/g' '{$importSql}'");

        // Prepare database initialization
        if ($environment['database']['engine'] === 'postgresql') {
            // Fix single quote escaping
            $this->exec("sed -i \"s/\\\'/''/g\" \"{$importSql}\"");
            $this->exec("echo '#!/bin/bash' > '{$importSh}'");
            $this->exec("echo 'set -e' >> '{$importSh}'");
            $this->exec("echo 'gosu postgres postgres --single -j {$environment['database']['name']} < /docker-entrypoint-initdb.d/{$environment['name']}.sql' > '{$importSh}'");
        } elseif ($environment['database']['engine'] === 'mysql') {
            // Re-format import.sql to match MySQLd init-file restrictions
            (new InitFileFixer())->fix($importSql);
        }

        $this->echo("Created database import script in {$importSql}", 'debug');

        // Setup web server
        $this->copy(
            "{$this->buildTemplates}/template/{$environment['server']['type']}/vhost.conf",
            "{$this->serverDockyard}/{$environment['server']['type']}/conf/{$domain}.conf",
            $this->filterExpand
        );
        $this->copy(
            "{$this->buildTemplates}/template/{$environment['server']['type']}/proxy.conf",
            "{$this->serverDockyard}/proxy/conf/{$domain}.conf",
            $this->filterExpand
        );

        // Create Joomla! configuration file
        if (file_exists("{$cmsRoot}/configuration.php-dist")) {
            $configFile = "{$cmsRoot}/configuration.php-dist";
        } else {
            $configFile = "{$cmsRoot}/installation/configuration.php-dist";
        }

        $errorReporting       = E_ALL & ~E_STRICT & ~E_DEPRECATED;
        $prettyServerName     = ucfirst($environment['server']['type']);
        $prettyDatabaseDriver = ucfirst(
            str_replace(
                [
                    'sql',
                    'my',
                ],
                [
                    'SQL',
                    'My',
                ],
                $environment['database']['driver']
            )
        );

        $map = [
            // Site Settings
            'sitename'        => "Joomla! {$version}/{$prettyServerName}/{$prettyDatabaseDriver}",
            // Database settings
            'dbtype'          => $environment['database']['driver'],
            'host'            => $environment['database']['engine'],
            'user'            => $database[$environment['database']['engine']]['user'],
            'password'        => $database[$environment['database']['engine']]['password'],
            'db'              => $environment['database']['name'],
            'dbprefix'        => $environment['database']['prefix'],
            // Server settings
            'error_reporting' => $errorReporting,
            // Locale settings
            'offset'          => $environment['server']['offset'],
            // Session settings
            'lifetime'        => $environment['session']['lifetime'],
            'session_handler' => $environment['session']['handler'],
            // Mail settings
            'mailer'          => 'smtp',
            'mailfrom'        => "admin@{$domain}",
            'fromname'        => "Joomla! {$version}/{$prettyServerName}/{$prettyDatabaseDriver}",
            'sendmail'        => '/usr/bin/env catchmail',
            'smtpauth'        => 0,
            'smtpuser'        => '',
            'smtppass'        => '',
            'smtphost'        => 'mail:1025',
            'smtpsecure'      => 'none',
            // Cache settings
            'caching'         => $environment['cache']['enabled'],
            'cachetime'       => $environment['cache']['time'],
            'cache_handler'   => $environment['cache']['handler'],
            // Debug settings
            'debug'           => $environment['debug']['system'],
            'debug_db'        => $environment['debug']['system'],
            'debug_lang'      => $environment['debug']['language'],
            // Meta settings
            'MetaDesc'        => $environment['meta']['description'],
            'MetaKeys'        => $environment['meta']['keywords'],
            'MetaTitle'       => $environment['meta']['showTitle'],
            'MetaAuthor'      => $environment['meta']['showAuthor'],
            'MetaVersion'     => $environment['meta']['showVersion'],
            // SEO settings
            'sef'             => $environment['sef']['enabled'],
            'sef_rewrite'     => $environment['sef']['rewrite'],
            'sef_suffix'      => $environment['sef']['suffix'],
            'unicodeslugs'    => $environment['sef']['unicode'],
            // Feed settings
            'feed_limit'      => $environment['feeds']['limit'],
            'feed_email'      => $environment['feeds']['email'],
        ];

        $this->copy(
            $configFile,
            "{$cmsRoot}/configuration.php",
            function ($content) use ($map) {
                foreach ($map as $key => $value) {
                    $pattern = "~(\{$this->$key}\s*=\s*)(.*?);(?:\s*//\s*(.*))?~";
                    $replace = "\1'{$value}'; // \3 was: \2";
                    $content = preg_replace(
                        $pattern,
                        $replace,
                        $content
                    );
                }

                return $content;
            }
        );

        // Remove installation folder
        $this->delete("{$cmsRoot}/installation");

        // A better way would be to change ownership within the containers
        $this->exec("chmod -R 0777 \"{$cmsRoot}\"");
    }

    /**
     * Get the environment settings
     *
     * @param          $projectName
     * @param          $environmentDefinition
     * @param  string  $xmlFile
     * @param  array   $database
     *
     * @return array
     */
    private function gatherEnvironment($projectName, $environmentDefinition, string $xmlFile, array $database): array
    {
        $target = basename($environmentDefinition, '.xml');

        $environment = [
            'name'     => $target,
            'server'   => [
                'type'   => 'nginx',
                'offset' => 'UTC',
                'tld'    => 'dev',
            ],
            'cache'    => [
                'enabled' => 0,
                'time'    => 15,
                'handler' => 'file',
            ],
            'debug'    => [
                'system'   => 1,
                'language' => 1,
            ],
            'meta'     => [
                'description' => "Test installation for {$projectName} on Joomla! \${version}",
                'keywords'    => "{$projectName} Joomla Test",
                'showVersion' => 1,
                'showTitle'   => 1,
                'showAuthor'  => 1,
            ],
            'sef'      => [
                'enabled' => 0,
                'rewrite' => 0,
                'suffix'  => 0,
                'unicode' => 0,
            ],
            'session'  => [
                'lifetime' => 15,
                'handler'  => 'database',
            ],
            'joomla'   => [
                'version'    => 'latest',
                'sampleData' => 'data',
            ],
            'database' => [
                'driver' => 'mysqli',
                'name'   => 'joomla_test',
                'prefix' => '${target}_',
            ],
            'feeds'    => [
                'limit' => 10,
                'email' => 'author',
            ],
        ];

        $environment = Array_::merge(
            $environment,
            Xml::xmlProperty($xmlFile, false, true)
        );
        $environment = Array_::merge(
            $environment,
            Xml::xmlProperty($environmentDefinition, false, true)
        );

        $environment['meta']['description'] = str_replace(
            '${version}',
            $environment['joomla']['version'],
            $environment['meta']['description']
        );
        $environment['database']['prefix']  = str_replace(
            '${target}',
            $environment['name'],
            $environment['database']['prefix']
        );

        if (in_array($environment['database']['driver'], ['mysqli', 'pdomysql'])) {
            $environment['database']['engine'] = 'mysql';
        } else {
            $environment['database']['engine'] = $environment['database']['driver'];
        }

        // Get the database info - use global values, if not provided with local environment
        $environment['database']['name'] = $environment['database']['name'] ?? $database[$environment['database']['engine']]['name'];

        return $environment;
    }
}
