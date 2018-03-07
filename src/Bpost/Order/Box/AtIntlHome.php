<?php

namespace Bpost\BpostApiClient\Bpost\Order\Box;

use Bpost\BpostApiClient\Bpost\Order\Box\CustomsInfo\CustomsInfo;
use Bpost\BpostApiClient\Bpost\Order\Box\Option\Messaging;
use Bpost\BpostApiClient\Bpost\Order\Receiver;
use Bpost\BpostApiClient\Bpost\ProductConfiguration\Product;
use Bpost\BpostApiClient\Exception\BpostNotImplementedException;

/**
 * bPost AtIntlPugo class
 *
 * @author    Tijs Verkoyen <php-bpost@verkoyen.eu>
 * @copyright Copyright (c), Tijs Verkoyen. All rights reserved.
 * @license   BSD License
 */
class AtIntlHome extends International
{
    /**
     * @return array
     */
    public static function getPossibleProductValues()
    {
        return array(
            Product::PRODUCT_NAME_BPACK_WORLD_BUSINESS,
            Product::PRODUCT_NAME_BPACK_WORLD_EASY_RETURN,
            Product::PRODUCT_NAME_BPACK_WORLD_EXPRESS_PRO,
            Product::PRODUCT_NAME_BPACK_EUROPE_BUSINESS,
        );
    }

    /**
     * @param \SimpleXMLElement $xml
     * @return International|static
     * @throws BpostNotImplementedException
     * @throws \Bpost\BpostApiClient\Exception\BpostLogicException\BpostInvalidLengthException
     * @throws \Bpost\BpostApiClient\Exception\BpostLogicException\BpostInvalidValueException
     */
    public static function createFromXML(\SimpleXMLElement $xml)
    {
        $international = new static();

        $data = $xml->children("http://schema.post.be/shm/deepintegration/v3/international");

        if (isset($data->product) && $data->product != '') {
            $international->setProduct(
                (string) $data->product
            );
        }
        if (isset($data->options)) {
            /** @var \SimpleXMLElement $optionData */
            foreach ($data->options as $options) {
                $options = $options->children('http://schema.post.be/shm/deepintegration/v3/common');

                foreach($options as $optionData){

                    if (in_array($optionData->getName(), Messaging::getPossibleTypeValues())) {

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

                    $international->addOption($option);
                }
            }
        }
        if (isset($data->parcelWeight) && $data->parcelWeight != '') {
            $international->setParcelWeight(
                (int) $data->parcelWeight
            );
        }
        if (isset($data->receiver)) {
            $receiverData = $data->receiver->children(
                'http://schema.post.be/shm/deepintegration/v3/common'
            );
            $international->setReceiver(
                Receiver::createFromXML($receiverData)
            );
        }
        if (isset($data->customsInfo)) {
            $international->setCustomsInfo(
                CustomsInfo::createFromXML($data->customsInfo)
            );
        }

        return $international;
    }


    public function toXML(\DOMDocument $document, $prefix = null)
    {
        $tagName = 'internationalBox';
        if ($prefix !== null) {
            $tagName = $prefix . ':' . $tagName;
        }

        $internationalBox = $document->createElement($tagName);
        $international = $document->createElement('international:atIntlHome');
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

        return $internationalBox;
    }

}