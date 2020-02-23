<?php

namespace GreenCape\JoomlaCLI\Command;

use RuntimeException;

class Docker
{
    private $container = '*';
    private $containerList;
    private $dir;
    private $state;

    private $supportedFileNames = ['docker-compose.yml', 'docker-compose.yaml', 'fig.yml', 'fig.yaml'];
    private $configFile;

    /**
     * Run as a task.
     *
     * @param $dir
     */
    public function __construct($dir)
    {
        $this->dir = $dir;
        $this->getContainerInfo();
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

        $replace   = ['?' => '.', '*' => '.*?'];
        $container = str_replace(array_keys($replace), array_values($replace), $this->container);
        $this->log("Searching containers matching '{$this->container}'", 'debug');

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
        $this->log(" - Found " . count($this->containerList) . ' containers', 'debug');
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
            throw new RuntimeException("Can't find a suitable configuration file. Are you in the right directory?\n\nSupported filenames: " . implode(', ',
                    $$this->supportedFileNames));
        }
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
     * @param  string  $message
     * @param  string  $level
     */
    private function log(string $message, string $level = 'info'): void
    {
        echo $message . "\n";
    }
}
