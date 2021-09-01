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

namespace GreenCape\JoomlaCLI\Command\Docker;

use DOMDocument;
use DOMNode;
use GreenCape\JoomlaCLI\Command;
use GreenCape\JoomlaCLI\Command\Core\DownloadCommand;
use GreenCape\JoomlaCLI\Command\Core\VersionCommand;
use GreenCape\JoomlaCLI\Fileset;
use GreenCape\JoomlaCLI\InitFileFixer;
use GreenCape\JoomlaCLI\Repository\VersionList;
use League\Flysystem\Adapter\Local;
use League\Flysystem\FileNotFoundException;
use League\Flysystem\Filesystem;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class BuildCommand
 *
 * @since  Class available since Release __DEPLOY_VERSION__
 */
class BuildCommand extends Command
{
    /**
     * Configure the options for the command
     *
     * @return  void
     */
    protected function configure(): void
    {
        $this->setName('docker:build')
             ->setDescription('Generates the contents and prepares the test containers')
             ->addBasePathOption()
        ;
    }

    /**
     * Execute the command
     *
     * @param  InputInterface   $input   An InputInterface instance
     * @param  OutputInterface  $output  An OutputInterface instance
     *
     * @throws FileNotFoundException
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        if (!file_exists($this->serverDockyard . '/docker-compose.yml')) {
            $uptodate = false;
        } else {
            $uptodate = $this->isUptodate(
                $this->serverDockyard . '/docker-compose.yml',
                (new Fileset('.'))->include($this->source . '/**')->include($this->integrationTests . '/**')->include(
                    $this->testEnvironments . '/**'
                )
            );
        }

        if ($uptodate) {
            $output->writeln('Container setups are up to date - skipping.');

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
        new VersionList(new Filesystem(new Local(dirname($this->versionCache))), basename($this->versionCache));

        // Set default values for keys not defined in database.xml
        $database = [
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
            ],
        ];

        // Load database environment, if provided
        if (file_exists($this->testEnvironments . '/database.xml')) {
            $database = $this->merge(
                $database,
                $this->xmlProperty($this->testEnvironments . '/database.xml', false, true)
            );
        }

        $database['mysql']['passwordOption'] = empty($database['mysql']['rootPassword']) ? ''
            : "-p'{$database['mysql']['rootPassword']}'";

        $this->copy((new Fileset($this->buildTemplates . '/docker'))->include('docker-compose.yml'),
                    $this->serverDockyard,
            function ($content) {
                return $this->expand($content);
            });

        // Handle each test environment
        foreach (glob($this->testEnvironments . '/*.xml') as $environmentDefinition) {
            if (in_array(basename($environmentDefinition), [
                'database.xml',
                'default.xml',
            ])) {
                continue;
            }

            $this->dockerBuildSystem($environmentDefinition, $database);
        }
    }

    /**
     * @param         $environmentDefinition
     * @param  array  $database
     *
     * @throws \Exception
     */
    private function dockerBuildSystem($environmentDefinition, array $database): void
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
                'handler' => 'file',
            ],
            'debug'    => [
                'system'   => 1,
                'language' => 1,
            ],
            'meta'     => [
                'description' => "Test installation for {$this->project['name']} on Joomla! \${version}",
                'keywords'    => "{$this->project['name']} Joomla Test",
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
                'prefix' => "{$target}_",
            ],
            'feeds'    => [
                'limit' => 10,
                'email' => 'author',
            ],
        ];

        $this->environment = $this->merge(
            $this->environment,
            $this->xmlProperty($this->testEnvironments . '/default.xml', false, true)
        );
        $this->environment = $this->merge($this->environment, $this->xmlProperty($environmentDefinition, false, true));

        $domain = "{$this->environment['name']}.{$this->environment['server']['tld']}";

        $this->environment['meta']['description'] = str_replace(
            '{$this->version}',
            $this->environment['joomla']['version'],
            $this->environment['meta']['description']
        );
        $this->environment['database']['prefix']  = str_replace(
            '{$this->target}',
            $this->environment['name'],
            $this->environment['database']['prefix']
        );

        if (in_array($this->environment['database']['driver'], [
            'mysqli',
            'pdomysql',
        ])) {
            $this->environment['database']['engine'] = 'mysql';
        } else {
            $this->environment['database']['engine'] = $this->environment['database']['driver'];
        }

        // Download and unpack the specified Joomla! version
        $cmsRoot = $this->serverDockyard . '/' . $this->environment['server']['type'] . '/html/' . $domain;

        $this->runCommand(
            DownloadCommand::class,
            new ArrayInput([
                               '--version' => $this->environment['joomla']['version'],
                               '--file'    => $this->versionCache,
                               '--cache'   => $this->downloadCache,
                           ]),
            $this->output
        );

        $output = new BufferedOutput();
        $this->runCommand(
            VersionCommand::class,
            new StringInput('--short'),
            $output
        );
        $version = trim($output->fetch());

        // Add SUT
        $this->copy(
            (new Fileset($this->source))->exclude('installation/**/*'),
            $cmsRoot
        );

        // Add test files
        $this->delete($cmsRoot . '/tests');
        $this->mkdir($cmsRoot . '/tests');
        $this->copy((new Fileset($this->tests))->include('mocks/**/*')
                                               ->include('integration/**/*')
                                               ->include('system/**/*')
                                               ->include('autoload.php'), $cmsRoot . '/tests', function ($content) {
            return $this->expand($content);
        });
        $this->copy(
            (new Fileset($this->buildTemplates . '/template/selenium'))->exclude('server_files/**/*'),
            $cmsRoot . '/tests/system'
        );

        $this->exec(
            "phpab --tolerant --basedir '.' --exclude '*Test.php' --template '$this->buildTemplates/template/tests/system/autoload.php.in' --output '$cmsRoot/tests/system/autoload.php' .",
            "$cmsRoot/tests/system"
        );

        // Create build directory
        $this->delete($cmsRoot . '/build');
        $this->mkdir($cmsRoot . '/build/logs/coverage');

        // Build the database import script
        if (!file_exists("$cmsRoot/installation/sql/{$this->environment['database']['engine']}")) {
            throw new \RuntimeException(
                "Joomla! $version does not support {$this->environment['database']['engine']} databases"
            );
        }

        // Get the database info - use global values, if not provided with local environment
        $this->environment['database']['name'] = $this->environment['database']['name'] ?? $database[$this->environment['database']['engine']]['name'];

        // Gather the database contents
        $coreData   = "$cmsRoot/installation/sql/{$this->environment['database']['engine']}/joomla.sql";
        $sampleData = "$cmsRoot/installation/sql/{$this->environment['database']['engine']}/sample_{$this->environment['joomla']['sampledata']}.sql";

        if (!file_exists($sampleData)) {
            throw new \RuntimeException(
                "No '{$this->environment['joomla']['sampledata']}' sample data found for Joomla! $version with {$this->environment['database']['engine']} database"
            );
        }

        $testData = $this->versionMatch(
            'joomla-(.*).sql',
            "$this->buildTemplates/template/{$this->environment['database']['engine']}",
            $version
        );

        if (empty($testData)) {
            throw new \RuntimeException(
                "No test data found for Joomla! $version with {$this->environment['database']['engine']} database"
            );
        }

        $this->output->writeln(
            <<<ECHO
    Joomla version:  $version
    Domain:          $domain
    Server:          {$this->environment['server']['type']}

    Database type:   {$this->environment['database']['engine']}:{$database[$this->environment['database']['engine']]['version']}
                     ({$this->environment['database']['driver']})
    Database name:   {$this->environment['database']['name']}
    Database prefix: {$this->environment['database']['prefix']}
    Database user:   {$database[$this->environment['database']['engine']]['user']}:{$database[$this->environment['database']['engine']]['password']}
ECHO
        );

        // Build the import files
        $importSql = "$this->serverDockyard/{$this->environment['database']['engine']}/{$this->environment['name']}.sql";
        $importSh  = "$this->serverDockyard/{$this->environment['database']['engine']}/{$this->environment['name']}.sh";

        if ($this->environment['database']['name'] === $database[$this->environment['database']['engine']]['name']) {
            file_put_contents($importSql, '');
        } else {
            $this->copy("$this->buildTemplates/template/{$this->environment['database']['engine']}/createdb.sql",
                        $importSql,
                function ($content) {
                    return $this->expand($content);
                });
        }

        $this->exec("cat '$coreData' >> '$importSql'");
        $this->exec("cat '$sampleData' >> '$importSql'");
        $this->exec("cat '$testData' >> '$importSql'");
        $this->exec("sed -i 's/#__/{$this->environment['database']['prefix']}/g' '$importSql'");

        // Prepare database initialization
        if ($this->environment['database']['engine'] === 'postgresql') {
            // Fix single quote escaping
            $this->exec("sed -i \"s/\\\'/''/g\" \"$importSql\"");
            $this->exec("echo '#!/bin/bash' > '$importSh'");
            $this->exec("echo 'set -e' >> '$importSh'");
            $this->exec(
                "echo 'gosu postgres postgres --single -j {$this->environment['database']['name']} < /docker-entrypoint-initdb.d/{$this->environment['name']}.sql' > '$importSh'"
            );
        } elseif ($this->environment['database']['engine'] === 'mysql') {
            // Re-format import.sql to match MySQLd init-file restrictions
            (new InitFileFixer())->fix($importSql);
        }

        $this->output->writeln(
            "Created database import script in $importSql",
            OutputInterface::VERBOSITY_VERY_VERBOSE
        );

        // Setup web server
        $this->copy("$this->buildTemplates/template/{$this->environment['server']['type']}/vhost.conf",
                    "$this->serverDockyard/{$this->environment['server']['type']}/conf/$domain.conf",
            function ($content) {
                return $this->expand($content);
            });
        $this->copy("$this->buildTemplates/template/{$this->environment['server']['type']}/proxy.conf",
                    "$this->serverDockyard/proxy/conf/$domain.conf",
            function ($content) {
                return $this->expand($content);
            });

        // Create Joomla! configuration file
        if (file_exists("$cmsRoot/configuration.php-dist")) {
            $configFile = "$cmsRoot/configuration.php-dist";
        } else {
            $configFile = "$cmsRoot/installation/configuration.php-dist";
        }

        $errorReporting       = E_ALL & ~E_STRICT & ~E_DEPRECATED;
        $prettyServerName     = ucfirst($this->environment['server']['type']);
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
                $this->environment['database']['driver']
            )
        );

        $map = [
            // Site Settings
            'sitename'        => "Joomla! $version/$prettyServerName/$prettyDatabaseDriver",
            // Database settings
            'dbtype'          => $this->environment['database']['driver'],
            'host'            => $this->environment['database']['engine'],
            'user'            => $database[$this->environment['database']['engine']]['user'],
            'password'        => $database[$this->environment['database']['engine']]['password'],
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
            'mailfrom'        => "admin@$domain",
            'fromname'        => "Joomla! $version/$prettyServerName/$prettyDatabaseDriver",
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

        $this->copy($configFile, "$cmsRoot/configuration.php", function ($content) use ($map) {
            foreach ($map as $key => $value) {
                $pattern = "~(->$key\s*=\s*)(.*?);(?:\s*//\s*(.*))?~";
                $replace = "\1'$value'; // \3 was: \2";
                $content = preg_replace(
                    $pattern,
                    $replace,
                    $content
                );
            }

            return $content;
        });

        // Remove installation folder
        $this->delete("$cmsRoot/installation");

        // A better way would be to change ownership within the containers
        $this->exec("chmod -R 0777 \"$cmsRoot\"");
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
    private function xmlProperty(string $xmlFile, bool $keepRoot = true, bool $collapseAttributes = false)
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
        } catch (\Throwable $exception) {
            throw new \RuntimeException("Unable to parse content of $xmlFile\n" . $exception->getMessage());
        }
    }

    /**
     * @param  DOMNode  $node
     * @param  bool     $collapseAttributes
     *
     * @return array|string
     */
    private function nodeToArray(DOMNode $node, bool $collapseAttributes)
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
}
