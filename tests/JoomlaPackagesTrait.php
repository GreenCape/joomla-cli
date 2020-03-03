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
 * @package         GreenCape\JoomlaCLI
 * @subpackage      Command
 * @author          Niels Braczek <nbraczek@bsds.de>
 * @copyright   (C) 2012-2019 GreenCape, Niels Braczek <nbraczek@bsds.de>
 * @license         http://opensource.org/licenses/MIT The MIT license (MIT)
 * @link            http://greencape.github.io
 * @since           File available since Release 0.1.1
 */

namespace GreenCapeTest;

trait JoomlaPackagesTrait
{
    /**
     * @return string[][]
     */
    public function joomlaPackages(): array
    {
        return [
            '1.0' => [
                'j10',
                '1.0',
                '1.0.0',
                'Joomla! 1.0.0 Stable [ Sunrise ] 17-Sep-2005 00:30 GMT',
            ],
            '1.5' => [
                'j15',
                '1.5',
                '1.5.26',
                'Joomla! 1.5.26 Stable [ senu takaa ama busani ] 27-March-2012 18:00 GMT',
            ],
            '1.7' => [
                'j17',
                '1.7',
                '1.7.3',
                'Joomla! 1.7.3 Stable [ Ember ] 14-Nov-2011 14:00 GMT',
            ],
            '2.5' => [
                'j25',
                '2.5',
                '2.5.0',
                'Joomla! 2.5.0 Stable [ Ember ] 24-Jan-2012 14:00 GMT',
            ],
            '3.5' => [
                'j35',
                '3.5',
                '3.5.0',
                'Joomla! 3.5.0 Stable [ Unicorn ] 21-March-2016 22:00 GMT',
            ],
            '3.9' => [
                'j39',
                '3.9',
                '3.9.11',
                'Joomla! 3.9.11 Stable [ Amani ] 13-August-2019 15:00 GMT',
            ],
        ];
    }

}
