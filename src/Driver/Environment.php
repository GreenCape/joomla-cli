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

namespace GreenCape\JoomlaCLI\Driver;

use Dotenv\Dotenv;

/**
 * Represents environment settings
 *
 * @property string name
 * @property array  server
 * @property array  database
 * @property mixed  joomla
 *
 * @since  Class available since Release __DEPLOY_VERSION__
 */
class Environment
{
    private $vars;

    /**
     * @var string
     */
    private $defFile;

    /**
     * @var string
     */
    private $envFile;

    /**
     * Environment constructor.
     *
     * @param  string  $defFile
     * @param  string  $envFile
     */
    public function __construct(string $defFile, string $envFile = '.env')
    {
        $this->defFile = $defFile;
        $this->envFile = $envFile;

        $this->init();
    }

    /**
     * @param $var
     *
     * @return mixed
     * @noinspection MagicMethodsValidityInspection
     */
    public function __get($var)
    {
        return $this->vars[$var];
    }

    /**
     *
     */
    private function init(): void
    {
        $this->vars = json_decode(
            file_get_contents(dirname(__DIR__, 2) . '/build/joomla/default.json'),
            JSON_OBJECT_AS_ARRAY
        );

        if (!empty($this->defFile)) {
            $this->vars = $this->merge(
                $this->vars,
                json_decode(file_get_contents($this->defFile), JSON_OBJECT_AS_ARRAY)
            );
        }

        if (file_exists($this->envFile)) {
            $env = Dotenv::createImmutable(dirname($this->envFile), basename($this->envFile));
            $env->load();
        }

        $this->mergeEnv($this->vars, 'JCLI');
    }

    /**
     * @param  array  $default
     * @param  array  $definition
     *
     * @return array
     */
    private function merge($default, $definition): array
    {
        $merged = $default;

        foreach ($definition as $key => $value) {
            if (is_array($value)) {
                $merged[$key] = $this->merge($merged[$key] ?? [], $value);
                continue;
            }

            $merged[$key] = $value;
        }

        return $merged;
    }

    /**
     * @param  array   $vars
     * @param  string  $prefix
     */
    private function mergeEnv(array &$vars, string $prefix): void
    {
        $envVars = getenv();

        foreach ($vars as $key => &$value) {
            $keyPrefix = $prefix . '_' . strtoupper($key);

            if (is_array($value)) {
                $this->mergeEnv($value, $keyPrefix);
                continue;
            }

            if (isset($envVars[$keyPrefix])) {
                $vars[$key] = $envVars[$keyPrefix];
            }
        }
    }
}
