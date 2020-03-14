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

namespace GreenCape\JoomlaCLI\Command\Document;

use Exception;
use GreenCape\JoomlaCLI\Command;
use GreenCape\JoomlaCLI\Documentation\API\APIGenerator;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class DocumentCommand
 *
 * @since  Class available since Release __DEPLOY_VERSION__
 */
class DocumentCommand extends Command
{
    /**
     * Configure the options for the command
     *
     * @return  void
     */
    protected function configure(): void
    {
        $this
            ->setName('document')
            ->setAliases(['document:api'])
            ->setDescription('Generates API documentation using the specified generator')
            ->addBasePathOption()
            ->addSourcePathOption()
            ->addLogPathOption()
            ->addOption(
                'generator',
                'g',
                InputArgument::OPTIONAL,
                'The API documentation generator to use (`apigen` or `phpdoc`)',
                'apigen'
            )
        ;
    }

    /**
     * Execute the command
     *
     * @param  InputInterface   $input   An InputInterface instance
     * @param  OutputInterface  $output  An OutputInterface instance
     *
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $report  = "{$this->build}/report/api";
        $umlPath = "{$this->build}/report/uml";

        $this->delete($report);
        $this->mkdir($report);

        $this->runCommand(
            UmlCommand::class,
            new ArrayInput(
                [
                    '--source' => $this->source,
                    '--output' => $umlPath,
                ]
            ),
            $output
        );

        $this->runCommand(
            ChangelogCommand::class,
            new ArrayInput(
                [
                ]
            ),
            $output
        );

        $generator = new APIGenerator($input->getOption('generator'));
        $generator->run(
            "{$this->project['name']} {$this->project['version']} API Documentation",
            $this->source,
            $report,
            $umlPath
        );
    }
}
