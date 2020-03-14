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

namespace GreenCape\JoomlaCLI\Command\Patch;

use GreenCape\JoomlaCLI\Command;
use GreenCape\JoomlaCLI\Fileset;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class CreateCommand
 *
 * @since  Class available since Release __DEPLOY_VERSION__
 */
class CreateCommand extends Command
{
    /**
     * Configure the options for the command
     *
     * @return  void
     */
    protected function configure(): void
    {
        $this
            ->setName('patch:create')
            ->setDescription('Creates a patch set ready to drop into an existing installation')
            ->addSourcePathOption()
            ->addOption(
                'force',
                'f',
                InputOption::VALUE_NONE,
                'Enforce patch creation regardless up-to-date status'
            )
        ;
    }

    /**
     * Execute the command
     *
     * @param  InputInterface   $input   An InputInterface instance
     * @param  OutputInterface  $output  An OutputInterface instance
     */
    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $patchsetLocation = $this->dist['basedir'] . '-full';

        $uptodate = $this->isUptodate(
            new Fileset($patchsetLocation),
            new Fileset($this->source)
        );

        if ($uptodate && !$input->getOption('force')) {
            $output->writeln("Patchset {$patchsetLocation} is up to date", OutputInterface::VERBOSITY_NORMAL);

            return;
        }

        $this->delete($patchsetLocation);
        $this->mkdir($patchsetLocation);

        $this->createPatchSet($this->package, $patchsetLocation);
    }

    /**
     * @param $package
     * @param $patchsetLocation
     */
    private function createPatchSet($package, $patchsetLocation): void
    {
        if ($package['type'] === 'package') {
            foreach ($package['extensions'] as $extension) {
                $this->createPatchSet($extension, $patchsetLocation);
            }

            $this->copy(
                ($this->source . '/' . $package['manifest']),
                $patchsetLocation . '/administrator/manifests/packages/' . strtolower($package['name']) . '.xml'
            );

            return;
        }

        if ($package['type'] === 'component') {
            $componentRoot = file_exists($this->source . '/component')
                ? $this->source . '/component'
                : $this->source;
            $shortName     = preg_replace('~^com_~', '', $package['name']);

            $this->copy(
                (new Fileset($componentRoot))
                    ->include('administrator/components/' . $package['name'] . '/**')
                    ->exclude('administrator/components/' . $package['name'] . '/language/**')
                    ->include('components/' . $package['name'] . '/**')
                    ->exclude('components/' . $package['name'] . '/language/**')
                    ->include('media/' . $package['name'] . '/**'),
                $patchsetLocation
            );

            $this->copy(
                (new Fileset($componentRoot . '/installation'))
                    ->include('**'),
                $patchsetLocation . '/administrator/components/' . $package['name']
            );

            $this->copy(
                (new Fileset($componentRoot))
                    ->include($shortName . '.xml'),
                $patchsetLocation . '/administrator/components/' . $package['name']
            );

            $this->copy(
                (new Fileset($componentRoot . '/administrator/components/' . $package['name'] ))
                    ->include('language/**'),
                $patchsetLocation . '/administrator'
            );

            $this->copy(
                (new Fileset($componentRoot . '/components/' . $package['name']))
                    ->include('language/**'),
                $patchsetLocation
            );

            return;
        }

        if ($package['type'] === 'module') {
            $this->copy(
                (new Fileset($this->source . '/modules'))
                    ->include($package['name'] . '/**')
                    ->exclude($package['name'] . '/language/**'),
                $patchsetLocation . '/modules'
            );

            $this->copy(
                (new Fileset($this->source . '/modules/' . $package['name'] . '/language'))
                    ->include('/**.ini'),
                $patchsetLocation . '/language'
            );

            return;
        }

        if ($package['type'] === 'plugin') {
            $shortName = preg_replace('~^plg_' . $package['group'] . '_~', '', $package['name']);

            $this->copy(
                (new Fileset($this->source . '/plugins/' . $package['group']))
                    ->include($shortName . '/**')
                    ->exclude($shortName . '/language/**'),
                $patchsetLocation . '/plugins/' . $package['group']
            );

            $this->copy(
                (new Fileset($this->source . '/plugins/' . $package['group'] . '/' . $shortName . '/language'))
                    ->include('/**.ini'),
                $patchsetLocation . '/administrator/language'
            );

            return;
        }
    }
}
