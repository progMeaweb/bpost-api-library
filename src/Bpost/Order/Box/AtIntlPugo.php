<?php

namespace Bpost\BpostApiClient\Bpost\Order\Box;

use Bpost\BpostApiClient\Bpost\Order\Box\CustomsInfo\CustomsInfo;
use Bpost\BpostApiClient\Bpost\Order\Box\Option\Messaging;
use Bpost\BpostApiClient\Bpost\Order\IntlPugoAddress;
use Bpost\BpostApiClient\Bpost\Order\PugoAddress;
use Bpost\BpostApiClient\Bpost\Order\Receiver;
use Bpost\BpostApiClient\Bpost\ProductConfiguration\Product;
use Bpost\BpostApiClient\Exception\BpostLogicException\BpostInvalidValueException;
use Bpost\BpostApiClient\Exception\BpostNotImplementedException;

/**
 * bPost AtIntlPugo class
 *
 * @author    Tijs Verkoyen <php-bpost@verkoyen.eu>
 * @copyright Copyright (c), Tijs Verkoyen. All rights reserved.
 * @license   BSD License
 */
class AtIntlPugo extends International
{

    /** @var string */
    private $pugoId;

    /** @var string */
    private $pugoName;

    /** @var PugoAddress */
    private $pugoAddress;

    /**
     * @return array
     */
    public static function getPossibleProductValues()
    {
        return array(
            Product::PRODUCT_NAME_BPACK_AT_BPOST_INTERNATIONAL,
        );
    }

    /**
     * @param PugoAddress $pugoAddress
     */
    public function setPugoAddress(PugoAddress $pugoAddress)
    {
        $this->pugoAddress = $pugoAddress;
    }

    /**
     * @return PugoAddress
     */
    public function getPugoAddress()
    {
        return $this->pugoAddress;
    }

    /**
     * @param string $pugoId
     */
    public function setPugoId($pugoId)
    {
        $this->pugoId = $pugoId;
    }

    /**
     * @return string
     */
    public function getPugoId()
    {
        return $this->pugoId;
    }

    /**
     * @param string $pugoName
     */
    public function setPugoName($pugoName)
    {
        $this->pugoName = $pugoName;
    }

    /**
     * @return string
     */
    public function getPugoName()
    {
        return $this->pugoName;
    }

    /**
     * Return the object as an array for usage in the XML
     *
     * @param  \DomDocument $document
     * @param  string       $prefix
     * @return \DomElement
     */
    public function toXML(\DOMDocument $document, $prefix = null)
    {
        $tagName = 'internationalBox';
        if ($prefix !== null) {
            $tagName = $prefix . ':' . $tagName;
        }

        $internationalBox = $document->createElement($tagName);
        $international = $document->createElement('international:atIntlPugo');
        $internationalBox->appendChild($international);

        if ($this->getProduct() !== null) {
            $international->appendChild(
                $document->createElement(
                    'international:product',
                    $this->getProduct()
                )
            );
        }

        $options = $this->getOptions();
        if (!empty($options)) {
            $optionsElement = $document->createElement('international:options');
            foreach ($options as $option) {
                $optionsElement->appendChild(
                    $option->toXML($document)
                );
            }
            $international->appendChild($optionsElement);
        }

        if ($this->getReceiver() !== null) {
            $international->appendChild(
                $this->getReceiver()->toXML($document, 'international')
            );
        }

        if ($this->getParcelWeight() !== null) {
            $international->appendChild(
                $document->createElement(
                    'international:parcelWeight',
                    $this->getParcelWeight()
                )
            );
        }

        if ($this->getCustomsInfo() !== null) {
            $international->appendChild(
                $this->getCustomsInfo()->toXML($document, 'international')
            );
        }

        if($this->getPugoId() !== null){
            $international->appendChild(
                $document->createElement(
                    'international:pugoId',
                    $this->getPugoId()
                )
            );
        }

        if($this->getPugoName() !== null){
            $international->appendChild(
                $document->createElement(
                    'international:pugoName',
                    $this->getPugoName()
                )
            );
        }

        if ($this->getPugoAddress() !== null) {

            $international->appendChild(
                $this->getPugoAddress()->toXML($document)
            );
        }

        return $internationalBox;
    }

    /**
     * @param \SimpleXMLElement $xml
     * @return AtIntlPugo|International
     * @throws BpostInvalidValueException
     * @throws BpostNotImplementedException
     * @throws \Bpost\BpostApiClient\Exception\BpostLogicException\BpostInvalidLengthException
     */
    public static function createFromXML(\SimpleXMLElement $xml)
    {
        $atIntlPugo = new AtIntlPugo();

        $data = $xml->children('http://schema.post.be/shm/deepintegration/v3/international');

        if (isset($data->product) && $data->product != '') {
            $atIntlPugo->setProduct(
                (string)$data->product
            );
        }

        if (isset($data->options)) {
            /** @var \SimpleXMLElement $optionData */
            foreach ($data->options as $options) {
                $options = $options->children('http://schema.post.be/shm/deepintegration/v3/common');

                foreach($options as $optionData){
                    if (in_array(
                        $optionData->getName(),
                        array(
                            Messaging::MESSAGING_TYPE_INFO_DISTRIBUTED,
                            Messaging::MESSAGING_TYPE_INFO_NEXT_DAY,
                            Messaging::MESSAGING_TYPE_INFO_REMINDER,
                            Messaging::MESSAGING_TYPE_KEEP_ME_INFORMED,
                        )
                    )
                    ) {
                        $option = Messaging::createFromXML($optionData);
                    } else {
                        $className = '\\Bpost\\BpostApiClient\\Bpost\\Order\\Box\\Option\\' . ucfirst($optionData->getName());
                        if (!method_exists($className, 'createFromXML')) {
                            throw new BpostNotImplementedException();
                        }
                        $option = call_user_func(
                            array($className, 'createFromXML'),
                            $optionData
                        );
                    }

                    $atIntlPugo->addOption($option);
                }
            }
        }

        if (isset($data->receiver)) {
            $receiverData = $data->receiver->children(
                'http://schema.post.be/shm/deepintegration/v3/common'
            );
            $atIntlPugo->setReceiver(
                Receiver::createFromXML($receiverData)
            );
        }

        if (isset($data->parcelWeight) && $data->parcelWeight != '') {
            $atIntlPugo->setParcelWeight(
                (int) $data->parcelWeight
            );
        }
        if (isset($data->customsInfo)) {
            $atIntlPugo->setCustomsInfo(
                CustomsInfo::createFromXML($data->customsInfo)
            );
        }

        if (isset($data->pugoId) && $data->pugoId != '') {
            $atIntlPugo->setPugoId(
                (string)$data->pugoId
            );
        }
        if (isset($data->pugoName) && $data->pugoName != '') {
            $atIntlPugo->setPugoName(
                (string)$data->pugoName
            );
        }
        if (isset($data->pugoAddress)) {
            /** @var \SimpleXMLElement $pugoAddressData */
            $pugoAddressData = $data->pugoAddress->children(
                'http://schema.post.be/shm/deepintegration/v3/common'
            );
            $atIntlPugo->setPugoAddress(
                IntlPugoAddress::createFromXML($pugoAddressData)
            );
        }

        return $atIntlPugo;
    }


}