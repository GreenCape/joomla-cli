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
 * @since           Class available since Release __DEPLOY_VERSION__
 */

namespace GreenCape\JoomlaCLI\Command;

use GreenCape\JoomlaCLI\Exception\DockerConfigurationNotFoundException;
use GreenCape\JoomlaCLI\Shell;
use RuntimeException;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class Docker
 *
 * @since  Class available since Release __DEPLOY_VERSION__
 */
class Docker
{
    private $container          = '*';

    private $containerList;

    private $dir;

    private $state;

    private $supportedFileNames = [
        'docker-compose.yml',
        'docker-compose.yaml',
        'fig.yml',
        'fig.yaml',
    ];

    private $configFile;

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * Run as a task.
     *
     * @param  OutputInterface  $output
     * @param  string           $dir
     */
    public function __construct(OutputInterface $output, string $dir)
    {
        $this->output = $output;
        $this->dir    = $dir;
        $this->getContainerInfo();
    }

    /**
     *
     */
    private function getContainerInfo(): void
    {
        $oldDir = getcwd();
        if (empty($this->dir)) {
            $this->dir = $oldDir;
        }
        chdir($this->dir);

        $this->checkConfigurationFile();

        $replace   = [
            '?' => '.',
            '*' => '.*?',
        ];
        $container = str_replace(array_keys($replace), array_values($replace), $this->container);
        $this->output->writeln(
            "Searching containers matching '{$this->container}'",
            OutputInterface::VERBOSITY_VERY_VERBOSE
        );

        $this->containerList = [];
        foreach (explode("\n", shell_exec('docker-compose ps')) as $line) {
            if (preg_match('~^(' . $container . ')\s+(.*?)\s+(\S+)\s+(\d.*)$~', $line, $match)) {
                $this->containerList[$match[1]] = [
                    'name'    => $match[1],
                    'command' => $match[2],
                    'state'   => strtolower($match[3]),
                    'ports'   => explode(', ', $match[4]),
                ];
            }
        }
        chdir($oldDir);
        $this->output->writeln(
            " - Found " . count($this->containerList) . ' containers',
            OutputInterface::VERBOSITY_VERY_VERBOSE
        );
    }

    /**
     *
     */
    private function checkConfigurationFile(): void
    {
        $this->configFile = null;

        foreach ($this->supportedFileNames as $filename) {
            if (file_exists($filename)) {
                $this->configFile = $filename;
                break;
            }
        }

        if (empty($this->configFile)) {
            throw new DockerConfigurationNotFoundException($this->supportedFileNames);
        }
    }

    /**
     * @param  string  $state
     *
     * @return Docker
     */
    public function state(string $state): self
    {
        $this->state = strtolower($state);

        return $this;
    }

    /**
     * @param  string  $container
     *
     * @return Docker
     */
    public function container(string $container): self
    {
        $this->container = $container;

        return $this;
    }

    /**
     * Get an array of all containers matching the conditions, i.e.,
     *   - name matches the pattern given in 'container'
     *   - state equals the value given in 'state'
     *
     * @return array
     */
    public function dockerList(): array
    {
        return array_keys($this->filterContainers($this->containerList));
    }

    /**
     * @param $availableContainers
     *
     * @return array
     */
    private function filterContainers($availableContainers): array
    {
        if ($this->state !== null) {
            $filteredContainers = [];
            foreach ($availableContainers as $container) {
                if ($container['state'] === $this->state) {
                    $filteredContainers[$container['name']] = $container;
                }
            }
        } else {
            $filteredContainers = $availableContainers;
        }

        return $filteredContainers;
    }

    /**
     * Get an array of all servers defined in the docker-compose (formerly called fig) configuration file
     *
     * @throws RuntimeException
     */
    public function dockerDef(): array
    {
        preg_match_all('~^(\w+):~m', file_get_contents($this->dir . '/' . $this->configFile), $match);

        return $match[1];
    }

    /**
     * Start the test containers
     *
     * @param  string  $options  Additional options for `docker-compose up`
     *
     * @throws RuntimeException
     */
    public function dockerUp(string $options = ''): void
    {
        (new Shell($this->output))->exec(
            'docker-compose up $options -d',
            $this->dir
        );

        // Give the containers time to set up
        sleep(15);
    }

    /**
     * Start the test containers
     */
    public function dockerStart(): void
    {
        $this->dockerUp('--no-recreate');
    }

    /**
     * Stop and removes the test containers
     *
     * @throws RuntimeException
     */
    public function dockerStop(): void
    {
        try {
            (new Shell($this->output))->exec(
                'docker-compose stop',
                $this->dir
            );

            // Give the containers time to stop
            sleep(2);
        } catch (DockerConfigurationNotFoundException $exception) {
            $this->output->writeln('Servers are not set up. Nothing to do');
        }
    }

    /**
     * Remove the content of test containers
     *
     * @throws RuntimeException
     */
    public function dockerRemove(): void
    {
        try {
            (new Shell($this->output))->exec(
                'docker-compose rm --force',
                $this->dir
            );
        } catch (DockerConfigurationNotFoundException $exception) {
            $this->output->writeln('Servers are not set up. Nothing to do');
        }
    }
}
