<?php
namespace Bpost\BpostApiClient\Bpost\Order\Box;

use Bpost\BpostApiClient\Bpost\Order\Box\National\ShopHandlingInstruction;
use Bpost\BpostApiClient\Bpost\Order\PugoAddress;
use Bpost\BpostApiClient\Bpost\ProductConfiguration\Product;
use Bpost\BpostApiClient\Bpost\Order\Box\Option\Messaging;
use Bpost\BpostApiClient\Exception\BpostLogicException\BpostInvalidValueException;
use Bpost\BpostApiClient\Exception\BpostNotImplementedException;

/**
 * bPost AtBpost class
 *
 * @author    Tijs Verkoyen <php-bpost@verkoyen.eu>
 * @copyright Copyright (c), Tijs Verkoyen. All rights reserved.
 * @license   BSD License
 */
class AtBpost extends National
{
    /** @var string */
    protected $product = Product::PRODUCT_NAME_BPACK_AT_BPOST;

    /** @var string */
    private $pugoId;

    /** @var string */
    private $pugoName;

    /** @var \Bpost\BpostApiClient\Bpost\Order\PugoAddress */
    private $pugoAddress;

    /** @var string */
    private $receiverName;

    /** @var string */
    private $receiverCompany;

    /** @var string */
    protected $requestedDeliveryDate;

    /** @var ShopHandlingInstruction */
    private $shopHandlingInstruction;

    /**
     * @param string $product Possible values are: bpack@bpost
     * @throws BpostInvalidValueException
     */
    public function setProduct($product)
    {
        if (!in_array($product, self::getPossibleProductValues())) {
            throw new BpostInvalidValueException('product', $product, self::getPossibleProductValues());
        }

        parent::setProduct($product);
    }

    /**
     * @return array
     */
    public static function getPossibleProductValues()
    {
        return array(
            Product::PRODUCT_NAME_BPACK_AT_BPOST,
        );
    }

    /**
     * @param \Bpost\BpostApiClient\Bpost\Order\PugoAddress $pugoAddress
     */
    public function setPugoAddress($pugoAddress)
    {
        $this->pugoAddress = $pugoAddress;
    }

    /**
     * @return \Bpost\BpostApiClient\Bpost\Order\PugoAddress
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
     * @param string $receiverCompany
     */
    public function setReceiverCompany($receiverCompany)
    {
        $this->receiverCompany = $receiverCompany;
    }

    /**
     * @return string
     */
    public function getReceiverCompany()
    {
        return $this->receiverCompany;
    }

    /**
     * @param string $receiverName
     */
    public function setReceiverName($receiverName)
    {
        $this->receiverName = $receiverName;
    }

    /**
     * @return string
     */
    public function getReceiverName()
    {
        return $this->receiverName;
    }

    /**
     * @return string
     */
    public function getRequestedDeliveryDate()
    {
        return $this->requestedDeliveryDate;
    }

    /**
     * @param string $requestedDeliveryDate
     */
    public function setRequestedDeliveryDate($requestedDeliveryDate)
    {
        $this->requestedDeliveryDate = $requestedDeliveryDate;
    }

    /**
     * @return string
     */
    public function getShopHandlingInstruction()
    {
        if ($this->shopHandlingInstruction !== null) {
            return $this->shopHandlingInstruction->getValue();
        }
        return null;
    }

    /**
     * @param string $shopHandlingInstruction
     */
    public function setShopHandlingInstruction($shopHandlingInstruction)
    {
        $this->shopHandlingInstruction = new ShopHandlingInstruction($shopHandlingInstruction);
    }

    /**
     * Return the object as an array for usage in the XML
     *
     * @param  \DomDocument $document
     * @param  string       $prefix
     * @param  string       $type
     * @return \DomElement
     */
    public function toXML(\DOMDocument $document, $prefix = null, $type = null)
    {
        $nationalElement = $document->createElement($this->getPrefixedTagName('nationalBox', $prefix));
        $boxElement = parent::toXML($document, null, 'atBpost');
        $nationalElement->appendChild($boxElement);

        if ($this->getPugoId() !== null) {
            $boxElement->appendChild(
                $document->createElement('pugoId', $this->getPugoId())
            );
        }
        if ($this->getPugoName() !== null) {
            $boxElement->appendChild(
                $document->createElement('pugoName', $this->getPugoName())
            );
        }
        if ($this->getPugoAddress() !== null) {
            $boxElement->appendChild(
                $this->getPugoAddress()->toXML($document, 'common')
            );
        }
        if ($this->getReceiverName() !== null) {
            $boxElement->appendChild(
                $document->createElement('receiverName', $this->getReceiverName())
            );
        }
        if ($this->getReceiverCompany() !== null) {
            $boxElement->appendChild(
                $document->createElement('receiverCompany', $this->getReceiverCompany())
            );
        }
        $this->addToXmlRequestedDeliveryDate($document, $boxElement, $prefix);
        $this->addToXmlShopHandlingInstruction($document, $boxElement, $prefix);

        return $nationalElement;
    }

    /**
     * @param \DOMDocument $document
     * @param \DOMElement  $typeElement
     * @param string       $prefix
     */
    protected function addToXmlRequestedDeliveryDate(\DOMDocument $document, \DOMElement $typeElement, $prefix)
    {
        if ($this->getRequestedDeliveryDate() !== null) {
            $typeElement->appendChild(
                $document->createElement('requestedDeliveryDate', $this->getRequestedDeliveryDate())
            );
        }
    }

    private function addToXmlShopHandlingInstruction(\DOMDocument $document, \DOMElement $typeElement, $prefix)
    {
        if ($this->getShopHandlingInstruction() !== null) {
            $typeElement->appendChild(
                $document->createElement('shopHandlingInstruction', $this->getShopHandlingInstruction())
            );
        }
    }

    /**
     * @param  \SimpleXMLElement $xml
     *
     * @return AtBpost
     * @throws BpostInvalidValueException
     * @throws BpostNotImplementedException
     */
    public static function createFromXML(\SimpleXMLElement $xml)
    {
        $atBpost = new AtBpost();

        $national = $xml->children('http://schema.post.be/shm/deepintegration/v3/national');

        if (isset($national->product) && $national->product != '') {
            $atBpost->setProduct(
                (string)$national->product
            );
        }
        if (isset($national->options)) {
            /** @var \SimpleXMLElement $options */
            foreach ($national->options as $options) {
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
                            throw new BpostNotImplementedException($className);
                        }
                        $option = call_user_func(
                            array($className, 'createFromXML'),
                            $optionData
                        );
                    }

                    $atBpost->addOption($option);
                }
            }
        }
        if (isset($national->weight) && $national->weight != '') {
            $atBpost->setWeight(
                (int)$national->weight
            );
        }
        if (isset($national->receiverName) && $national->receiverName != '') {
            $atBpost->setReceiverName(
                (string)$national->receiverName
            );
        }
        if (isset($national->receiverCompany) && $national->receiverCompany != '') {
            $atBpost->setReceiverCompany(
                (string)$national->receiverCompany
            );
        }
        if (isset($national->pugoId) && $national->pugoId != '') {
            $atBpost->setPugoId(
                (string)$national->pugoId
            );
        }
        if (isset($national->pugoName) && $national->pugoName != '') {
            $atBpost->setPugoName(
                (string)$national->pugoName
            );
        }
        if (isset($national->pugoAddress)) {
            /** @var \SimpleXMLElement $pugoAddressData */
            $pugoAddressData = $national->pugoAddress->children(
                'http://schema.post.be/shm/deepintegration/v3/common'
            );
            $atBpost->setPugoAddress(
                PugoAddress::createFromXML($pugoAddressData)
            );
        }
        if (isset($national->requestedDeliveryDate) && $national->requestedDeliveryDate != '') {
            $atBpost->setRequestedDeliveryDate(
                (string)$national->requestedDeliveryDate
            );
        }
        if (isset($national->shopHandlingInstruction) && $national->shopHandlingInstruction != '') {
            $atBpost->setShopHandlingInstruction(
                (string)$national->shopHandlingInstruction
            );
        }

        return $atBpost;
    }
}
