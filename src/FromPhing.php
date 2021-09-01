<?php

namespace GreenCape\JoomlaCLI;

use DOMDocument;
use DOMNode;
use GreenCape\JoomlaCLI\Command\Docker;
use GreenCape\JoomlaCLI\Command\Docker\StartCommand;
use GreenCape\JoomlaCLI\Command\Docker\StopCommand;
use GreenCape\JoomlaCLI\Repository\VersionList;
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

    use FilesystemMethods;

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
        $this->init($basedir ?? '.', $projectFile ?? 'project.json');
    }

    /**
     * Updates the build environment
     */
    public function selfUpdate(): void
    {
        $this->exec('git pull origin && composer update', $this->build);
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
     * @throws \Exception
     */
    public function testIntegration(): void
    {
        (new StartCommand())->run(new StringInput(''), $this->output);

        $environments = (new Fileset($this->testEnvironments))
            ->include('*.xml')
            ->exclude('database.xml')
            ->exclude('default.xml')
            ->getFiles()
        ;

        foreach ($environments as $environmentDefinition) {
            $this->testIntegrationSingle($environmentDefinition);
        }

        (new StopCommand())->run(new StringInput(''), $this->output);
    }

    /**
     * Runs system tests on all test installations.
     *
     * @throws FileNotFoundException
     * @throws \Exception
     */
    public function testSystem(): void
    {
        (new StartCommand())->run(new StringInput(''), $this->output);

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

        (new StopCommand())->run(new StringInput(''), $this->output);
    }

    /**
     * REPLACED IN COMMAND
     */
    private function testCoverageReport(): void
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

        $this->package['name']     = $settings['package']['name'] ?? 'com_' . strtolower(
                preg_replace(
                    '~\W+~',
                    '_',
                    $this->project['name']
                )
            );
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
                    $this->package['extensions'][$extension['@id']]['archive'] = ltrim(
                        ($extension['@base'] ?? '') . '/' . $extension['file'],
                        '/'
                    );
                    $this->package['extensions'][$extension['@id']]['type']    = $extension['@type'];
                }
            }
        } else {
            $this->echo("Manifest file '{$settings['package']['manifest']}' not found.", 'warning');
        }

        $this->echo(
            ucfirst($this->package['type']) . " {$this->package['name']} {$this->package['version']}",
            'verbose'
        );

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

    /******************
     * Internal tasks *
     ******************/

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

        $this->output->writeln(
            strtoupper($level) . ': ' . str_replace($this->basedir, '.', $message),
            $verbosity[$level]
        );
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
            function ($content) {
                $content = str_replace(
                    '</head>',
                    '<script type="text/javascript" src="../js/jquery_plantuml.js"></script></head>',
                    $content
                );
                $content = preg_replace(
                    "~<th>startuml</th>(\n)<td>(.+?)</td>~sm",
                    /** @lang text */
                    '<th>UML</th><td><img uml="\\1!include ' . $this->build . '/report/api/uml/skin.puml\\1\\2\\1" alt=""/></td>',
                    $content
                );
                $content = preg_replace("~<tr>\s*<th>enduml</th>\s*<td></td>\s*</tr>~m", '', $content);

                return $content;
            }
        );
    }

    /**
     * Generate autoload script
     */
    private function phpAb(): void
    {
        $this->exec("{$this->bin}/phpab --tolerant --basedir . --output {$this->tests}/autoload.php {$this->tests}");
        $this->exec(
            "{$this->bin}/phpab --tolerant --basedir {$this->source} --output {$this->source}/autoload.php {$this->source}"
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
     * Run integrations tests on a single test installation
     *
     * @param $environmentDefinition
     */
    private function testIntegrationSingle($environmentDefinition): void
    {
        $target = basename($environmentDefinition, '.xml');

        // Get the environment settings
        $environment = $this->merge(
            $this->merge(
                [
                    'name'   => $target,
                    'server' => [
                        'type' => 'nginx',
                        'tld'  => 'dev',
                    ],
                ],
                $this->xmlProperty("{$this->testEnvironments}/default.xml", false, true)
            ),
            $this->xmlProperty("{$this->testEnvironments}/{$environmentDefinition}", false, true)
        );
        $domain      = "{$environment['name']}.{$this->environment['server']['tld']}";
        $cmsRoot     = "{$this->serverDockyard}/{$environment['server']['type']}/html/{$domain}";

        $container = $environment['server']['type'] === 'nginx' ? 'servers_php_1' : "servers_{$environment['server']['type']}_1";

        $uptodate = $this->isUptodate(
            "{$this->build}/logs/coverage/integration-{$target}.cov",
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

        $applications = (new Fileset("{$cmsRoot}/tests/integration"))->include(
            '*',
            Fileset::NO_RECURSE | Fileset::ONLY_DIRS
        );

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
     * Runs system tests on a single test installation.
     *
     * @param $environmentDefinition
     */
    private function testSystemSingle($environmentDefinition): void
    {
        $target = basename($environmentDefinition, '.xml');

        // Get the environment settings
        $environment = $this->merge(
            $this->merge(
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
                $this->xmlProperty("{$this->testEnvironments}/default.xml", false, true)
            ),
            $this->xmlProperty("{$this->testEnvironments}/{$environmentDefinition}", false, true)
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

        $this->exec(
            "docker exec --user={$this->user} {$container} /bin/bash -c \"cd /var/www/html/{$domain}/tests/system; /usr/local/lib/php/vendor/bin/phpunit\""
        );

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
     * @param $array1
     * @param $array2
     *
     * @return array
     */
    private function merge($array1, $array2): array
    {
        foreach ($array2 as $key => $value) {
            $array1[$key] = is_array($value) ? $this->merge((array)$array1[$key], $value) : $value;
        }

        return $array1;
    }

    /**
     * @param  string  $xmlFile
     * @param  bool    $keepRoot
     * @param  bool    $collapseAttributes
     *
     * @return array|string
     */
    private function xmlProperty(string $xmlFile, $keepRoot = true, $collapseAttributes = false)
    {
        $prolog     = '<?xml version="1.0" encoding="UTF-8"?>';
        $xmlContent = file_get_contents($xmlFile);
        if (strpos($xmlContent, '<?xml') !== 0) {
            $xmlContent = $prolog . "\n" . $xmlContent;
        }

        try {
            $xml = new DOMDocument();
            $xml->loadXML($xmlContent);

            $node = $xml->firstChild;

            $array = $this->nodeToArray($node, $collapseAttributes);

            if ($keepRoot) {
                $array = [
                    $node->nodeName => $array,
                ];
            }

            return $array;
        } catch (Throwable $exception) {
            throw new RuntimeException("Unable to parse content of {$xmlFile}\n" . $exception->getMessage());
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

        $this->exec(
            "docker exec --user={$this->user} {$container} /bin/bash -c \"cd /var/www/html/{$domain}/tests/integration/{$application}; /usr/local/lib/php/vendor/bin/phpunit\""
        );
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
     * @param  DOMNode  $node
     * @param  bool     $collapseAttributes
     *
     * @return array|string
     */
    private function nodeToArray(DOMNode $node, $collapseAttributes = false)
    {
        $array = [];

        if ($node->hasAttributes()) {
            foreach ($node->attributes as $attr) {
                if ($collapseAttributes) {
                    $array[$attr->nodeName] = $attr->nodeValue;
                } else {
                    $array['.attributes'][$attr->nodeName] = $attr->nodeValue;
                }
            }
        }

        if ($node->hasChildNodes()) {
            foreach ($node->childNodes as $childNode) {
                if ($childNode->nodeType === XML_TEXT_NODE) {
                    $value = trim($childNode->nodeValue);
                    if (!empty($value)) {
                        return $value;
                    }
                } else {
                    $array[$childNode->nodeName] = $this->nodeToArray($childNode, $collapseAttributes);
                }
            }
        }

        return $array;
    }
}
