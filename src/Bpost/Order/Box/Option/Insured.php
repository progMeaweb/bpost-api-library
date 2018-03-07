<?php
namespace Bpost\BpostApiClient\Bpost\Order\Box\Option;

use Bpost\BpostApiClient\Exception\BpostLogicException\BpostInvalidValueException;

/**
 * bPost Insured class
 *
 * @author    Tijs Verkoyen <php-bpost@verkoyen.eu>
 * @copyright Copyright (c), Tijs Verkoyen. All rights reserved.
 * @license   BSD License
 */
class Insured extends Option
{

    const INSURANCE_TYPE_BASIC_INSURANCE = 'basicInsurance';
    const INSURANCE_TYPE_ADDITIONAL_INSURANCE = 'additionalInsurance';

    const INSURANCE_AMOUNT_UP_TO_500_EUROS = 1;
    const INSURANCE_AMOUNT_UP_TO_2500_EUROS = 2;
    const INSURANCE_AMOUNT_UP_TO_5000_EUROS = 3;
    const INSURANCE_AMOUNT_UP_TO_7500_EUROS = 4;
    const INSURANCE_AMOUNT_UP_TO_10000_EUROS = 5;
    const INSURANCE_AMOUNT_UP_TO_12500_EUROS = 6;
    const INSURANCE_AMOUNT_UP_TO_15000_EUROS = 7;
    const INSURANCE_AMOUNT_UP_TO_17500_EUROS = 8;
    const INSURANCE_AMOUNT_UP_TO_20000_EUROS = 9;
    const INSURANCE_AMOUNT_UP_TO_22500_EUROS = 10;
    const INSURANCE_AMOUNT_UP_TO_25000_EUROS = 11;

    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $value;

    /**
     * @return array
     */
    public static function getPossibleTypeValues()
    {
        return array(
            self::INSURANCE_TYPE_BASIC_INSURANCE,
            self::INSURANCE_TYPE_ADDITIONAL_INSURANCE,
        );
    }

    /**
     * @param string $type
     * @throws BpostInvalidValueException
     */
    public function setType($type)
    {
        if (!in_array($type, self::getPossibleTypeValues())) {
            throw new BpostInvalidValueException('type', $type, self::getPossibleTypeValues());
        }

        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $value
     * @throws BpostInvalidValueException
     */
    public function setValue($value)
    {
        if (!in_array($value, self::getPossibleValueValues())) {
            throw new BpostInvalidValueException('value', $value, self::getPossibleValueValues());
        }

        $this->value = $value;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return array
     */
    public static function getPossibleValueValues()
    {
        return array(
            self::INSURANCE_AMOUNT_UP_TO_500_EUROS,
            self::INSURANCE_AMOUNT_UP_TO_2500_EUROS,
            self::INSURANCE_AMOUNT_UP_TO_5000_EUROS,
            self::INSURANCE_AMOUNT_UP_TO_7500_EUROS,
            self::INSURANCE_AMOUNT_UP_TO_10000_EUROS,
            self::INSURANCE_AMOUNT_UP_TO_12500_EUROS,
            self::INSURANCE_AMOUNT_UP_TO_15000_EUROS,
            self::INSURANCE_AMOUNT_UP_TO_17500_EUROS,
            self::INSURANCE_AMOUNT_UP_TO_20000_EUROS,
            self::INSURANCE_AMOUNT_UP_TO_22500_EUROS,
            self::INSURANCE_AMOUNT_UP_TO_25000_EUROS,
        );
    }

    /**
     * Return the object as an array for usage in the XML
     *
     * @param  \DomDocument $document
     * @param  string       $prefix
     * @return \DomElement
     */
    public function toXML(\DOMDocument $document, $prefix = 'common')
    {
        $tagName = 'insured';
        if ($prefix !== null) {
            $tagName = $prefix . ':' . $tagName;
        }
        $insured = $document->createElement($tagName);

        $tagName = $this->getType();
        if ($prefix !== null) {
            $tagName = $prefix . ':' . $tagName;
        }
        $insurance = $document->createElement($tagName);
        $insured->appendChild($insurance);

        if ($this->getValue() !== null) {
            $insurance->setAttribute('value', $this->getValue());
        }

        return $insured;
    }

    /**
     * @param \SimpleXMLElement $xml
     * @return static
     * @throws BpostInvalidValueException
     */
    public static function createFromXML(\SimpleXMLElement $xml)
    {
        $type = '';
        $value = 0;

        $data = $xml->children('http://schema.post.be/shm/deepintegration/v3/common');

        if (isset($data->{static::INSURANCE_TYPE_BASIC_INSURANCE})) {
            $type = static::INSURANCE_TYPE_BASIC_INSURANCE;
            $value = (int)$data->{static::INSURANCE_TYPE_BASIC_INSURANCE}->attributes()->value;
        }
        elseif (isset($data->{static::INSURANCE_TYPE_ADDITIONAL_INSURANCE})) {
            $value = (int)$data->{static::INSURANCE_TYPE_ADDITIONAL_INSURANCE}->attributes()->value;
            $type = static::INSURANCE_TYPE_ADDITIONAL_INSURANCE;
        }

        $insured = new static();
        $insured->setType($type);
        $insured->setValue($value);

        return $insured;
    }
}