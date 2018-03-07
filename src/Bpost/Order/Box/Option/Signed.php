<?php
namespace Bpost\BpostApiClient\Bpost\Order\Box\Option;

/**
 * bPost Signed class
 *
 * @author    Tijs Verkoyen <php-bpost@verkoyen.eu>
 * @copyright Copyright (c), Tijs Verkoyen. All rights reserved.
 * @license   BSD License
 */
class Signed extends Option
{
    /**
     * Return the object as an array for usage in the XML
     *
     * @param  \DomDocument $document
     * @param  string       $prefix
     * @return \DomElement
     */
    public function toXML(\DOMDocument $document, $prefix = 'common')
    {
        $tagName = 'signed';
        if ($prefix !== null) {
            $tagName = $prefix . ':' . $tagName;
        }

        return $document->createElement($tagName);
    }


    public static function createFromXML(\SimpleXMLElement $element)
    {
        return new static();
    }
}
