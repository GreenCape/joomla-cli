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
 * @since           File available since Release __DEPLOY_VERSION__
 */

namespace GreenCape\JoomlaCLI\Utility;

use DOMDocument;
use DOMNode;
use RuntimeException;
use Throwable;

/**
 * XML convenience methods from Phing
 *
 * @since       Class available since Release __DEPLOY_VERSION__
 */
class Xml
{
    /**
     * @param  string  $xmlFile
     * @param  bool    $keepRoot
     * @param  bool    $collapseAttributes
     *
     * @return array|string
     */
    public static function xmlProperty(string $xmlFile, $keepRoot = true, $collapseAttributes = false)
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

            $array = self::nodeToArray($node, $collapseAttributes);

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
     * @param  DOMNode  $node
     * @param  bool     $collapseAttributes
     *
     * @return array|string
     */
    private static function nodeToArray(DOMNode $node, $collapseAttributes = false)
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
                    $array[$childNode->nodeName] = self::nodeToArray($childNode, $collapseAttributes);
                }
            }
        }

        return $array;
    }
}
