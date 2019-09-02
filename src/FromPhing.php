<?php

use GreenCape\JoomlaCLI\Command\Docker;
use GreenCape\JoomlaCLI\CoverageMerger;
use GreenCape\JoomlaCLI\Documentation\API\APIGenerator;
use GreenCape\JoomlaCLI\Fileset;
use GreenCape\JoomlaCLI\InitFileFixer;
use GreenCape\JoomlaCLI\Repository\VersionList;
use GreenCape\JoomlaCLI\UMLGenerator;
use GreenCape\Manifest\Manifest;
use League\Flysystem\Adapter\Local;
use League\Flysystem\FileExistsException;
use League\Flysystem\FileNotFoundException;
use League\Flysystem\Filesystem;
use Symfony\Component\Console\Output\OutputInterface;

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
	 * [extensions] => Optional list of extensions for packages, indexed by name (inclo. type prefix)
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
	 * @param OutputInterface $output
	 * @param                 $basedir
	 * @param                 $projectFile
	 */
	public function __construct(OutputInterface $output, $basedir = null, $projectFile = null)
	{
		$this->output = $output;
		$this->init(realpath($basedir ?? '.'), $projectFile ?? 'project.json');
	}

	/**
	 * Performs all tests and generates documentation and the quality report.
	 *
	 * @throws FileExistsException
	 * @throws FileNotFoundException
	 */
	public function build(): void
	{
		$this->prepare();
		$this->test();
		$this->quality();
		$this->document();
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
	 * Create artifact directories
	 */
	private function prepare(): void
	{
		$this->mkdir("{$this->build}/logs");
	}

	/**
	 * Generate autoload script
	 */
	private function phpAb(): void
	{
		foreach (['administrator/components', 'components'] as $target)
		{
			$this->createAutoloader($target);
		}

		$this->exec("{$this->bin}/phpab --tolerant --basedir . --output autoload.php .", $this->tests);
	}

	/**
	 * @param string $target
	 */
	private function createAutoloader(string $target): void
	{
		if (!file_exists("{$this->source}/{$target}"))
		{
			return;
		}

		$this->echo("Creating autoloader for {$this->source}/{$target}/{$this->package['name']}", 'info');

		$this->exec("{$this->bin}/phpab --tolerant --basedir . --output autoload.php --template autoload.php.in .", "{$this->source}/{$target}/{$this->package['name']}");
	}

	/** @noinspection PhpUnused */
	/**
	 * Updates the build environment
	 */
	public function selfUpdate(): void
	{
		$this->clean();
		$this->exec('git pull origin && composer update', $this->build);
	}

	/************************
	 * Docker related tasks *
	 ************************/

	/**
	 * Generates the contents and prepares the test containers.
	 *
	 * @throws FileExistsException
	 * @throws FileNotFoundException
	 */
	public function dockerBuild(): void
	{
		if (!file_exists($this->serverDockyard . '/docker-compose.yml'))
		{
			$uptodate = false;
		}
		else
		{
			$uptodate = $this->isUptodate(
				$this->serverDockyard . '/docker-compose.yml',
				(new Fileset('.'))
					->include($this->source . '/**')
					->include($this->integrationTests . '/**')
					->include($this->testEnvironments . '/**')
			);
		}

		if ($uptodate)
		{
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

		// Set default values for keys not defined in database.xml
		$this->database = [
			'mysql'      => [
				'version'      => 'latest',
				'name'         => 'joomla_test',
				'user'         => 'db_user',
				'password'     => 'db_pass',
				'rootPassword' => '',
			],
			'postgresql' => [
				'version'  => 'latest',
				'name'     => 'joomla_test',
				'user'     => 'db_user',
				'password' => 'db_pass',
			]
		];

		// Load database environment, if provided
		if (file_exists($this->testEnvironments . '/database.xml'))
		{
			$this->database = $this->merge($this->database, $this->xmlProperty($this->testEnvironments . '/database.xml', false, true));
		}

		$this->database['mysql']['passwordOption'] = empty($this->database['mysql']['rootPassword'])
			? ''
			: "-p'{$this->database['mysql']['rootPassword']}'";

		$this->copy(
			(new Fileset($this->buildTemplates . '/docker'))
				->include('docker-compose.yml'),
			$this->serverDockyard,
			$this->filterExpand
		);

		// Handle each test environment
		foreach (glob($this->testEnvironments . '/*.xml') as $environmentDefinition)
		{
			if (in_array(basename($environmentDefinition), ['database.xml', 'default.xml']))
			{
				continue;
			}

			$this->dockerBuildSystem($environmentDefinition);
		}
	}

	/**
	 * Starts the test containers, building them only if not existing.
	 *
	 * @throws FileExistsException
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
	 * Starts the test containers after rebuilding them.
	 */
	public function dockerUp(): void
	{
		if (!file_exists("{$this->serverDockyard}/docker-compose.yml"))
		{
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
	 * Stops and removes the test containers.
	 */
	public function dockerStop(): void
	{
		if (!file_exists("{$this->serverDockyard}/docker-compose.yml"))
		{
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
	 * Removes the content of test containers.
	 */
	public function dockerRemove(): void
	{
		if (!file_exists("{$this->serverDockyard}/docker-compose.yml"))
		{
			$this->echo('Servers are not set up. Nothing to do', 'info');

			return;
		}

		$this->exec(
			'docker-compose rm --force',
			$this->serverDockyard
		);
		$this->delete($this->serverDockyard);
	}

	/**
	 * @param $environmentDefinition
	 *
	 * @throws FileExistsException
	 * @throws FileNotFoundException
	 */
	private function dockerBuildSystem($environmentDefinition): void
	{
		$target = basename($environmentDefinition, '.xml');

		// Get the environment settings
		$this->environment = [
			'name'     => $target,
			'server'   => [
				'type'   => 'nginx',
				'offset' => 'UTC',
				'tld'    => 'dev',
			],
			'cache'    => [
				'enabled' => 0,
				'time'    => 15,
				'handler' => 'file'
			],
			'debug'    => [
				'system'   => 1,
				'language' => 1
			],
			'meta'     => [
				'description' => "Test installation for {$this->project['name']} on Joomla! \${version}",
				'keywords'    => "{$this->project['name']} Joomla Test",
				'showVersion' => 1,
				'showTitle'   => 1,
				'showAuthor'  => 1
			],
			'sef'      => [
				'enabled' => 0,
				'rewrite' => 0,
				'suffix'  => 0,
				'unicode' => 0
			],
			'session'  => [
				'lifetime' => 15,
				'handler'  => 'database'
			],
			'joomla'   => [
				'version'    => 'latest',
				'sampleData' => 'data'
			],
			'database' => [
				'driver' => 'mysqli',
				'name'   => 'joomla_test',
				'prefix' => "{$target}_"
			],
			'feeds'    => [
				'limit' => 10,
				'email' => 'author'
			]
		];

		$this->environment = $this->merge($this->environment, $this->xmlProperty($this->testEnvironments . '/default.xml', false, true));
		$this->environment = $this->merge($this->environment, $this->xmlProperty($environmentDefinition, false, true));

		$domain = "{$this->environment['name']}.{$this->environment['server']['tld']}";

		$this->environment['meta']['description'] = str_replace('{$this->version}', $this->environment['joomla']['version'], $this->environment['meta']['description']);
		$this->environment['database']['prefix']  = str_replace('{$this->target}', $this->environment['name'], $this->environment['database']['prefix']);

		if (in_array($this->environment['database']['driver'], ['mysqli', 'pdomysql']))
		{
			$this->environment['database']['engine'] = 'mysql';
		}
		else
		{
			$this->environment['database']['engine'] = $this->environment['database']['driver'];
		}

		// Download and unpack the specified Joomla! version
		$cmsRoot = $this->serverDockyard . '/' . $this->environment['server']['type'] . '/html/' . $domain;
		$tarball = $this->joomlaDownload($this->environment['joomla']['version'], $this->versionCache, $this->downloadCache);
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
		if (!file_exists("{$cmsRoot}/installation/sql/{$this->environment['database']['engine']}"))
		{
			throw new RuntimeException("Joomla! {$version} does not support {$this->environment['database']['engine']} databases");
		}

		// Get the database info - use global values, if not provided with local environment
		$this->environment['database']['name'] = $this->environment['database']['name'] ?? $this->database[$this->environment['database']['engine']]['name'];

		// Gather the database contents
		$coreData   = "{$cmsRoot}/installation/sql/{$this->environment['database']['engine']}/joomla.sql";
		$sampleData = "{$cmsRoot}/installation/sql/{$this->environment['database']['engine']}/sample_{$this->environment['joomla']['sampledata']}.sql";

		if (!file_exists($sampleData))
		{
			throw new RuntimeException("No '{$this->environment['joomla']['sampledata']}' sample data found for Joomla! {$version} with {$this->environment['database']['engine']} database");
		}

		$testData = $this->versionMatch(
			'joomla-(.*).sql',
			"{$this->buildTemplates}/template/{$this->environment['database']['engine']}",
			$version
		);

		if (empty($testData))
		{
			throw new RuntimeException("No test data found for Joomla! {$version} with {$this->environment['database']['engine']} database");
		}

		$this->echo(<<<ECHO
    Joomla version:  {$version}
    Domain:          {$domain}
    Server:          {$this->environment['server']['type']}

    Database type:   {$this->environment['database']['engine']}:{$this->database[$this->environment['database']['engine']]['version']}
                     ({$this->environment['database']['driver']})
    Database name:   {$this->environment['database']['name']}
    Database prefix: {$this->environment['database']['prefix']}
    Database user:   {$this->database[$this->environment['database']['engine']]['user']}:{$this->database[$this->environment['database']['engine']]['password']}
ECHO
			,
			'info'
		);

		// Build the import files
		$importSql = "{$this->serverDockyard}/{$this->environment['database']['engine']}/{$this->environment['name']}.sql";
		$importSh  = "{$this->serverDockyard}/{$this->environment['database']['engine']}/{$this->environment['name']}.sh";

		if ($this->environment['database']['name'] === $this->database[$this->environment['database']['engine']]['name'])
		{
			file_put_contents($importSql, '');
		}
		else
		{
			$this->copy(
				"{$this->buildTemplates}/template/{$this->environment['database']['engine']}/createdb.sql",
				$importSql,
				$this->filterExpand
			);
		}

		$this->exec("cat '{$coreData}' >> '{$importSql}'");
		$this->exec("cat '{$sampleData}' >> '{$importSql}'");
		$this->exec("cat '{$testData}' >> '{$importSql}'");
		$this->exec("sed -i 's/#__/{$this->environment['database']['prefix']}/g' '{$importSql}'");

		// Prepare database initialization
		if ($this->environment['database']['engine'] === 'postgresql')
		{
			// Fix single quote escaping
			$this->exec("sed -i \"s/\\\'/''/g\" \"{$importSql}\"");
			$this->exec("echo '#!/bin/bash' > '{$importSh}'");
			$this->exec("echo 'set -e' >> '{$importSh}'");
			$this->exec("echo 'gosu postgres postgres --single -j {$this->environment['database']['name']} < /docker-entrypoint-initdb.d/{$this->environment['name']}.sql' > '{$importSh}'");
		}
		elseif ($this->environment['database']['engine'] === 'mysql')
		{
			// Re-format import.sql to match MySQLd init-file restrictions
			(new InitFileFixer())->fix($importSql);
		}

		$this->echo("Created database import script in {$importSql}", 'debug');

		// Setup web server
		$this->copy(
			"{$this->buildTemplates}/template/{$this->environment['server']['type']}/vhost.conf",
			"{$this->serverDockyard}/{$this->environment['server']['type']}/conf/{$domain}.conf",
			$this->filterExpand
		);
		$this->copy(
			"{$this->buildTemplates}/template/{$this->environment['server']['type']}/proxy.conf",
			"{$this->serverDockyard}/proxy/conf/{$domain}.conf",
			$this->filterExpand
		);

		// Create Joomla! configuration file
		if (file_exists("{$cmsRoot}/configuration.php-dist"))
		{
			$configFile = "{$cmsRoot}/configuration.php-dist";
		}
		else
		{
			$configFile = "{$cmsRoot}/installation/configuration.php-dist";
		}

		$errorReporting       = E_ALL & ~E_STRICT & ~E_DEPRECATED;
		$prettyServerName     = ucfirst($this->environment['server']['type']);
		$prettyDatabaseDriver = ucfirst(
			str_replace(
				[
					'sql',
					'my'
				],
				[
					'SQL',
					'My'
				],
				$this->environment['database']['driver']
			)
		);

		$map = [
			// Site Settings
			'sitename'        => "Joomla! {$version}/{$prettyServerName}/{$prettyDatabaseDriver}",
			// Database settings
			'dbtype'          => $this->environment['database']['driver'],
			'host'            => $this->environment['database']['engine'],
			'user'            => $this->database[$this->environment['database']['engine']]['user'],
			'password'        => $this->database[$this->environment['database']['engine']]['password'],
			'db'              => $this->environment['database']['name'],
			'dbprefix'        => $this->environment['database']['prefix'],
			// Server settings
			'error_reporting' => $errorReporting,
			// Locale settings
			'offset'          => $this->environment['server']['offset'],
			// Session settings
			'lifetime'        => $this->environment['session']['lifetime'],
			'session_handler' => $this->environment['session']['handler'],
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
			'caching'         => $this->environment['cache']['enabled'],
			'cachetime'       => $this->environment['cache']['time'],
			'cache_handler'   => $this->environment['cache']['handler'],
			// Debug settings
			'debug'           => $this->environment['debug']['system'],
			'debug_db'        => $this->environment['debug']['system'],
			'debug_lang'      => $this->environment['debug']['language'],
			// Meta settings
			'MetaDesc'        => $this->environment['meta']['description'],
			'MetaKeys'        => $this->environment['meta']['keywords'],
			'MetaTitle'       => $this->environment['meta']['showTitle'],
			'MetaAuthor'      => $this->environment['meta']['showAuthor'],
			'MetaVersion'     => $this->environment['meta']['showVersion'],
			// SEO settings
			'sef'             => $this->environment['sef']['enabled'],
			'sef_rewrite'     => $this->environment['sef']['rewrite'],
			'sef_suffix'      => $this->environment['sef']['suffix'],
			'unicodeslugs'    => $this->environment['sef']['unicode'],
			// Feed settings
			'feed_limit'      => $this->environment['feeds']['limit'],
			'feed_email'      => $this->environment['feeds']['email'],
		];

		$this->copy(
			$configFile,
			"{$cmsRoot}/configuration.php",
			function ($content) use ($map) {
				foreach ($map as $key => $value)
				{
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

	/*******************************
	 * Documentation related tasks *
	 *******************************/

	/**
	 * Generates API documentation using the specified generator.
	 *
	 * @param $apidocGenerator
	 */
	public function document($apidocGenerator = null): void
	{
		$apidocGenerator = $apidocGenerator ?? 'apigen'; // Supported generators: phpdoc, apigen;
		$this->documentClean();
		$this->documentUml();
		$this->documentChangelog();

		$generator = new APIGenerator($apidocGenerator ?? 'apigen');
		$generator->run("{$this->project['name']} {$this->project['version']} API Documentation", $this->source, $this->build . '/report/api', '../uml');
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
	 * Generates CHANGELOG.md from the git commit history.
	 */
	public function documentChangelog(): void
	{
		$this->exec("git log --pretty=format:'%+d %ad [%h] %s (%an)' --date=short > {$this->basedir}/CHANGELOG.md");
		$this->reflexive(
			(new Fileset($this->basedir))
				->include('CHANGELOG.md'),
			static function ($content) {
				$content = preg_replace("~(\n)\s*\(([^)]+)\)~", "\1\1 Version \2\1------\1\1", $content);
				$content = preg_replace("~(\n) +~", "\1", $content);
				$content = preg_replace("~(\n)(\d)~", "\1    \2", $content);
				$content = preg_replace("~^(\n)~", "Changelog\1=========\1", $content);

				return $content;
			}
		);
	}

	/**
	 * @param bool $keepSources
	 */
	public function documentUml(bool $keepSources = false): void
	{
		$this->delete("{$this->build}/report/uml");
		$this->mkdir("{$this->build}/report/uml");
		$this->copy("{$this->buildTemplates}/config/plantuml/skin-bw-gradient.puml", "{$this->build}/report/uml/skin.puml");

		$this->copy( // @todo make this version aware
			(new Fileset("{$this->buildTemplates}/plantuml/joomla-3"))->include('*.puml'),
			"{$this->build}/report/uml"
		);

		$uml   = new UMLGenerator("{$this->buildTemplates}/plantuml/plantuml.jar");
		$files = $uml->generate(
			(new Fileset($this->source))
				->include('**/*')
				->getFiles(),
			"{$this->build}/report/uml"
		);

		if (!$keepSources)
		{
			$this->delete(
				(new Fileset("{$this->build}/report/uml"))
					->include('*.puml')
					->include('*.svg')
					->exclude(array_map(function ($file) {
							return preg_replace(
								'~\.puml$~',
								'.svg',
								$file
							);
						},
							$files
						)
					)
			);
		}
	}

	/** @noinspection PhpUnusedPrivateMethodInspection */
	/**
	 * Generate API documentation using PHPDocumentor2
	 *
	 * @param $apidocTitle
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
				$content = str_replace('</head>', '<script type="text/javascript" src="../js/jquery_plantuml.js"></script></head>', $content);
				$content = preg_replace("~<th>startuml</th>(\n)<td>(.+?)</td>~sm", "<th>UML</th><td><img uml=\"\\1!include {$this->build}/report/api/uml/skin.puml\\1\\2\\1\" alt=''/></td>", $content);
				$content = preg_replace("~<tr>\s*<th>enduml</th>\s*<td></td>\s*</tr>~m", '', $content);

				return $content;
			}
		);
	}

	/*********************************
	 * Quality Metrics related tasks *
	 *********************************/

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

	/***************************
	 * Patch Set related tasks *
	 ***************************/

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

		if ($uptodate)
		{
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

		if ($this->package['type'] === 'com_')
		{
			$this->copy(
				(new Fileset($this->source))
					->include('installation/**'),
				"{$patchsetLocation}/administrator/components/com_{$this->package['name']}"
			);
		}
	}

	/**********************
	 * Test related tasks *
	 **********************/

	/**
	 * Runs all tests locally and in the test containers.
	 *
	 * @throws FileExistsException
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
	 */
	public function testUnit(): void
	{
		if (!file_exists("{$this->tests}/unit/bootstrap.php"))
		{
			// Find bootstrap file
			$bootstrap = $this->versionMatch(
				'bootstrap-(.*).php',
				"{$this->buildTemplates}/template/tests/unit",
				$this->package['target']
			);

			if (empty($bootstrap))
			{
				throw new RuntimeException("No bootstrap file found for Joomla! {$this->package['target']}");
			}

			$this->copy($bootstrap, "{$this->tests}/unit/bootstrap.php");
		}

		if (!file_exists("{$this->tests}/unit/bootstrap.php"))
		{
			$this->copy("{$this->buildTemplates}/template/tests/unit/autoload.php", "{$this->tests}/unit/autoload.php");
		}

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
	 * @throws FileExistsException
	 * @throws FileNotFoundException
	 */
	public function testIntegration(): void
	{
		$this->dockerStart();

		$environments = (new Fileset($this->testEnvironments))
			->include('*.xml')
			->exclude('database.xml')
			->exclude('default.xml')
			->getFiles();

		foreach ($environments as $environmentDefinition)
		{
			$this->testIntegrationSingle($environmentDefinition);
		}

		$this->dockerStop();
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
						'tld'  => 'dev'
					]
				],
				$this->xmlProperty("{$this->testEnvironments}/default.xml", false, true)
			),
			$this->xmlProperty("{$this->testEnvironments}/{$environmentDefinition}", false, true)
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

		if ($uptodate)
		{
			$this->echo("Integration test for {$target} is up to date - skipping.", 'info');

			return;
		}

		$this->echo("Integration test on {$target}", 'info');
		$this->delete("{$cmsRoot}/build/logs");
		$this->mkdir("{$cmsRoot}/build/logs");

		$applications = (new Fileset("{$cmsRoot}/tests/integration"))->include('*', Fileset::NO_RECURSE | Fileset::ONLY_DIRS);

		foreach ($applications as $application)
		{
			$this->testIntegrationApp($application, $domain, $cmsRoot, $target, $container);
		}

		$merger = new CoverageMerger();
		$merger
			->fileset((new Fileset("{$cmsRoot}/build/logs"))->include('**/*.cov'))
			->pattern("/var/www/html/{$domain}")
			->replace("{$this->source}/")
			->php("{$cmsRoot}/build/logs/integration-{$target}.cov")
			->merge();

		$this->copy(
			new Fileset("{$cmsRoot}/build/logs/integration-{$target}.cov"),
			"{$this->build}/logs/coverage",
			static function ($content) use ($target) {
				return preg_replace("~'(.*?)Test::test~", "'{$target}: \1Test::test", $content);
			}
		);
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
		if (empty($application))
		{
			return;
		}

		$integrationTestFilter = static function ($content) use ($application, $domain, $target) {
			return str_replace(
				[
					'@APPLICATION@',
					'@CMS_ROOT@',
					'@TARGET@'
				],
				[
					$application,
					"/var/www/html/{$domain}",
					$target
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

		if (empty($bootstrap))
		{
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
	 * Runs system tests on all test installations.
	 *
	 * @throws FileExistsException
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
			->getFiles();

		foreach ($environments as $environmentDefinition)
		{
			$this->testSystemSingle($environmentDefinition);
		}

		$this->dockerStop();
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
						'tld'  => 'dev'
					],
					'browser' => [
						'type' => 'firefox'
					]
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
					$environment['browser']['type']
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

		if (empty($bootstrap))
		{
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
			->merge();

		$this->reflexive(
			new Fileset("{$this->build}/report/coverage"),
			function ($content) {
				return str_replace($this->source, $this->project['name'], $content);
			}
		);
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

	/******************************
	 * Distribution related tasks *
	 ******************************/

	/**
	 * Generate the distribution
	 *
	 * @throws FileExistsException
	 * @throws FileNotFoundException
	 */
	public function dist(): void
	{
		$this->build();
		$this->distPrepare();

		$packageName = "{$this->package['name']}-{$this->project['version']}";
		$this->exec("zip -r ../packages/{$packageName}.zip * > /dev/null", $this->dist['basedir']);
		$this->exec("tar --create --gzip --file ../packages/{$packageName}.tar.gz * > /dev/null", $this->dist['basedir']);
		$this->exec("tar --create --bzip2 --file ../packages/{$packageName}.tar.bz2 * > /dev/null", $this->dist['basedir']);
	}

	/**
	 * Cleanup distribution directory
	 */
	public function distClean(): void
	{
		$this->delete($this->dist['basedir']);
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

	/******************
	 * Internal tasks *
	 ******************/

	/**
	 * Initialise.
	 *
	 * @param string $dir         The absolute path to the project root
	 * @param string $projectFile The path to the project file, relative to $dir
	 */
	private function init($dir, $projectFile): void
	{
		$this->basedir = $dir;
		$this->user    = getmyuid() . ':' . getmygid();

		if (!file_exists($this->basedir . '/' . $projectFile))
		{
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

		$this->package['name']     = $settings['package']['name'] ?? 'com_' . strtolower(preg_replace('~\W+~', '_', $this->project['name']));
		$this->package['type']     = $settings['package']['type'] ?? 'component';
		$this->package['manifest'] = $settings['package']['manifest'] ?? 'manifest.xml';
		$this->package['version']  = $settings['package']['version'] ?? $this->project['version'];

		if (isset($settings['package']['extensions']))
		{
			foreach ($settings['package']['extensions'] as $extension)
			{
				$extension['version']                            = $extension['version'] ?? $this->package['version'];
				$this->package['extensions'][$extension['name']] = $extension;
			}
		}

		$this->echo("Project: {$this->project['name']} {$this->project['version']}", 'verbose');

		$this->source = rtrim($this->basedir . '/' . $this->project['paths']['source'] ?? 'source', '/');

		if (file_exists($this->source . '/' . $settings['package']['manifest']))
		{
			$this->echo("Reading manifest file {$settings['package']['manifest']}", 'debug');

			$manifest = Manifest::load($this->source . '/' . $settings['package']['manifest']);

			$this->package['name'] = $manifest->getName();
			$this->package['type'] = $manifest->getType();

			if ($manifest->getType() === 'package')
			{
				foreach ($manifest->getSection('files')->getStructure() as $extension)
				{
					$this->package['extensions'][$extension['@id']]['archive'] = ltrim(($extension['@base'] ?? '') . '/' . $extension['file'], '/');
					$this->package['extensions'][$extension['@id']]['type']    = $extension['@type'];
				}
			}
		}
		else
		{
			$this->echo("Manifest file '{$settings['package']['manifest']}' not found.", 'warning');
		}

		$this->echo(ucfirst($this->package['type']) . " {$this->package['name']} {$this->package['version']}", 'verbose');

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
			'port' => 9000
		];

		if (empty($this->project['name']))
		{
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
	 * @param string $command
	 * @param string $dir
	 * @param bool   $passthru
	 *
	 * @return string|null
	 */
	private function exec(string $command, string $dir = '.', bool $passthru = true): ?string
	{
		$this->echo("Running `{$command}` in `{$dir}`", 'debug');

		if ($dir !== '.')
		{
			$current = getcwd();
			$command = 'cd ' . $dir . ' && ' . $command . ' && cd ' . $current;
		}

		$result = '';

		if ($passthru)
		{
			passthru($command . ' 2>&1', $result);
		}
		else
		{
			$result = shell_exec($command . ' 2>&1');
		}

		return $result;
	}

	/**
	 * @param string $dir
	 */
	private function mkdir(string $dir): void
	{
		$this->exec("mkdir --mode=0775 --parents $dir", $this->basedir);
	}

	/**
	 * Checks if (every file from) target is newer than (every file from) source
	 *
	 * @param Fileset|string $target
	 * @param Fileset|string ...$sources
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

		foreach ($sources as $source)
		{
			$sourceFiles = is_string($source) ? [$source] : $source->getFiles();
			foreach ($sourceFiles as $file)
			{
				if (filemtime($file) > $targetTime)
				{
					return false;
				}
			}
		}

		return true;
	}

	/**
	 * @param string $message
	 * @param string $level
	 */
	private function echo(string $message, string $level): void
	{
		$verbosity = [
			'info'    => OutputInterface::VERBOSITY_NORMAL,
			'warning' => OutputInterface::VERBOSITY_NORMAL,
			'error'   => OutputInterface::VERBOSITY_NORMAL,
			'verbose' => OutputInterface::VERBOSITY_VERBOSE,
			'debug'   => OutputInterface::VERBOSITY_DEBUG
		];

		$this->output->writeln(strtoupper($level) . ': ' . str_replace($this->basedir, '.', $message), $verbosity[$level]);
	}

	/**
	 * @param Fileset|string $fileset
	 */
	private function delete($fileset): void
	{
		if (is_string($fileset))
		{
			$this->deleteFile($fileset);

			return;
		}

		foreach ($fileset->getFiles() as $file)
		{
			$this->deleteFile($file);
		}
	}

	/**
	 * @param $file
	 */
	private function deleteFile($file): void
	{
		if (!file_exists($file))
		{
			return;
		}

		$this->exec(is_dir($file) ? "rm -rf $file" : "rm $file");
	}

	/**
	 * @param $versionCache
	 *
	 * @return VersionList
	 * @throws FileExistsException
	 * @throws FileNotFoundException
	 */
	private function joomlaVersions($versionCache): VersionList
	{
		return new VersionList(new Filesystem(new Local(dirname($versionCache))), basename($versionCache));
	}

	/**
	 * @param DOMNode $node
	 * @param bool    $collapseAttributes
	 *
	 * @return array|string
	 */
	private function nodeToArray(DOMNode $node, $collapseAttributes = false)
	{
		$array = [];

		if ($node->hasAttributes())
		{
			foreach ($node->attributes as $attr)
			{
				if ($collapseAttributes)
				{
					$array[$attr->nodeName] = $attr->nodeValue;
				}
				else
				{
					$array['.attributes'][$attr->nodeName] = $attr->nodeValue;
				}
			}
		}

		if ($node->hasChildNodes())
		{
			foreach ($node->childNodes as $childNode)
			{
				if ($childNode->nodeType === XML_TEXT_NODE)
				{
					$value = trim($childNode->nodeValue);
					if (!empty($value))
					{
						return $value;
					}
				}
				else
				{
					$array[$childNode->nodeName] = $this->nodeToArray($childNode, $collapseAttributes);
				}
			}
		}

		return $array;
	}

	/**
	 * @param string $xmlFile
	 * @param bool   $keepRoot
	 * @param bool   $collapseAttributes
	 *
	 * @return array|string
	 */
	private function xmlProperty(string $xmlFile, $keepRoot = true, $collapseAttributes = false)
	{
		$prolog     = '<?xml version="1.0" encoding="UTF-8"?>';
		$xmlContent = file_get_contents($xmlFile);
		if (strpos($xmlContent, '<?xml') !== 0)
		{
			$xmlContent = $prolog . "\n" . $xmlContent;
		}

		try
		{
			$xml = new DOMDocument();
			$xml->loadXML($xmlContent);

			$node = $xml->firstChild;

			$array = $this->nodeToArray($node, $collapseAttributes);

			if ($keepRoot)
			{
				$array = [
					$node->nodeName => $array
				];
			}

			return $array;
		}
		catch (Throwable $exception)
		{
			throw new RuntimeException("Unable to parse content of {$xmlFile}\n" . $exception->getMessage());
		}
	}

	/**
	 * @param $array1
	 * @param $array2
	 *
	 * @return array
	 */
	private function merge($array1, $array2): array
	{
		foreach ($array2 as $key => $value)
		{
			$array1[$key] = is_array($value) ? $this->merge((array) $array1[$key], $value) : $value;
		}

		return $array1;
	}

	/**
	 * @param Fileset|string $fileset
	 * @param string         $toDir
	 * @param callable|null  $filter
	 */
	private function copy($fileset, string $toDir, callable $filter = null): void
	{
		if (is_string($fileset))
		{
			$this->copyFile($fileset, $toDir, $filter);

			return;
		}

		foreach ($fileset->getFiles() as $file)
		{
			$this->copyFile($file, str_replace($fileset->getDir(), $toDir, $file), $filter);
		}
	}

	/**
	 * @param string        $file
	 * @param string        $toFile
	 * @param callable|null $filter
	 */
	private function copyFile(string $file, string $toFile, callable $filter = null): void
	{
		if (is_dir($file))
		{
			return;
		}

		$this->echo("Copying {$file}" . ($filter !== null ? ' with filter' : '') . " to {$toFile}", 'debug');

		$content = file_get_contents($file);

		if (is_callable($filter))
		{
			$content = $filter($content);
		}

		file_put_contents($toFile, $content);
	}

	/**
	 * @param string $content
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

				for ($index = array_shift($parts); $index !== null; $index = array_shift($parts))
				{
					$var = $var[$index];
				}

				return $var;
			},
			$content
		);
	}

	/**
	 * @param        $version
	 * @param string $versionCache
	 * @param string $downloadCache
	 *
	 * @return string
	 * @throws FileExistsException
	 * @throws FileNotFoundException
	 */
	private function joomlaDownload($version, string $versionCache, string $downloadCache): string
	{
		$versions  = $this->joomlaVersions($versionCache);
		$requested = $version;
		$version   = $versions->resolve($version);
		$tarball   = $downloadCache . '/' . $version . '.tar.gz';

		if (!$versions->isBranch($version) && file_exists($tarball))
		{
			return $tarball;
		}

		if ($versions->isBranch($version))
		{
			$url = 'http://github.com/joomla/joomla-cms/tarball/' . $version;

			return $this->download($tarball, $url);
		}

		if ($versions->isTag($version))
		{
			try // to get the official release for that version
			{
				$url = "https://github.com/joomla/joomla-cms/releases/download/{$version}/Joomla_{$version}-Stable-Full_Package.tar.gz";

				return $this->download($tarball, $url);
			}
			catch (Throwable $exception) // else get it from the archive
			{
				$repository = $versions->getRepository($version);
				$url        = 'https://github.com/' . $repository . '/archive/' . $version . '.tar.gz';

				return $this->download($tarball, $url);
			}
		}

		throw new RuntimeException("$requested: Version is unknown");
	}

	/**
	 * @param string $filename
	 * @param string $url
	 *
	 * @return string
	 */
	private function download(string $filename, string $url): string
	{
		$bytes = file_put_contents($filename, @fopen($url, 'rb'));

		if ($bytes === false || $bytes === 0)
		{
			throw new RuntimeException("Failed to download $url");
		}

		return $filename;
	}

	/**
	 * @param string $toDir
	 * @param string $file
	 */
	private function untar(string $toDir, string $file): void
	{
		$this->mkdir($toDir);
		$this->exec("tar -zxvf {$file} -C {$toDir} --exclude-vcs");

		// If $toDir contains only a single directory, we need to lift everything up one level.
		$dirList = glob("{$toDir}/*", GLOB_ONLYDIR);

		if (count($dirList) === 1)
		{
			$this->copy(
				new Fileset($dirList[0]),
				$toDir
			);

			$this->delete($dirList[0]);
		}
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
		foreach (glob("$path/*") as $filename)
		{
			if (preg_match("/{$pattern}/", $filename, $match) && version_compare($bestVersion, $match[1], '<') && version_compare($match[1], $version, '<='))
			{
				$bestVersion = $match[1];
				$bestFile    = $filename;
			}
		}

		return $bestFile;
	}

	/**
	 * @param Fileset|string $fileset
	 * @param callable       $filter
	 */
	private function reflexive($fileset, callable $filter): void
	{
		if (is_string($fileset))
		{
			$this->copyFile($fileset, $fileset, $filter);

			return;
		}

		foreach ($fileset->getFiles() as $file)
		{
			$this->copyFile($file, $file, $filter);
		}
	}
}
