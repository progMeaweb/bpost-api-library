<?php
namespace Bpost\BpostApiClient\Bpost\Order\Box\Option;

/**
 * bPost AutomaticSecondPresentation class
 *
 * @author    Tijs Verkoyen <php-bpost@verkoyen.eu>
 * @copyright Copyright (c), Tijs Verkoyen. All rights reserved.
 * @license   BSD License
 */
class AutomaticSecondPresentation extends Option
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
        $tagName = 'automaticSecondPresentation';
        if ($prefix !== null) {
            $tagName = $prefix . ':' . $tagName;
        }

        return $document->createElement($tagName);
    }

    /**
     * @param \SimpleXMLElement $xml
     *
     * @return static
     */
    public static function createFromXML(\SimpleXMLElement $xml)
    {
        return new static;
    }
}
