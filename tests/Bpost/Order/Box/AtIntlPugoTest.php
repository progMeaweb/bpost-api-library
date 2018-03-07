<?php

namespace Bpost;


use Bpost\BpostApiClient\Bpost\Order\Address;
use Bpost\BpostApiClient\Bpost\Order\Box\AtIntlPugo;
use Bpost\BpostApiClient\Bpost\Order\Box\CustomsInfo\CustomsInfo;
use Bpost\BpostApiClient\Bpost\Order\Box\Option\Messaging;
use Bpost\BpostApiClient\Bpost\Order\Box\Option\Signed;
use Bpost\BpostApiClient\Bpost\Order\IntlPugoAddress;
use Bpost\BpostApiClient\Bpost\Order\Receiver;
use Bpost\BpostApiClient\Exception\BpostLogicException\BpostInvalidValueException;

class AtIntlPugoTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Create a generic DOM Document
     *
     * @return \DOMDocument
     */
    private static function createDomDocument()
    {
        $document = new \DOMDocument('1.0', 'UTF-8');
        $document->preserveWhiteSpace = false;
        $document->formatOutput = true;

        return $document;
    }

    /**
     * Test validation in the setters
     */
    public function testFaultyProperties()
    {
        $at247 = new AtIntlPugo();

        try {
            $at247->setProduct(str_repeat('a', 10));
            $this->fail('BpostInvalidValueException not launched');
        } catch (BpostInvalidValueException $e) {
            // Nothing, the exception is good
        } catch (\Exception $e) {
            $this->fail('BpostInvalidValueException not caught');
        }

        // Exceptions were caught,
        $this->assertTrue(true);
    }


    public function testToXml()
    {
        $box = new AtIntlPugo();

        $box->setProduct('bpack@bpost international');

        $box->addOption(new Messaging(Messaging::MESSAGING_TYPE_KEEP_ME_INFORMED, 'NL', 'someone@somedomain.be'));
        $box->addOPtion(new Signed());

        $receiver = new Receiver();
        $receiver->setCompany('SOME COMPANY');
        $receiver->setName('SOME NAME');
        $receiver->setEmailAddress('someone@somedomain.be');
        $receiver->setPhoneNumber('123456789');

        $address = new Address();
        $address->setStreetName('SOME STREET');
        $address->setNumber('999');
        $address->setPostalCode('99999');
        $address->setLocality('SIN CITY');
        $address->setCountryCode('FR');
        $receiver->setAddress($address);

        $box->setReceiver($receiver);


        $box->setParcelWeight(1234);

        $box->setPugoId('123');
        $box->setPugoName('SOME PUGO');

        $pugo = new IntlPugoAddress();
        $pugo->setStreetName('PUGO STREET');
        $pugo->setNumber('555');
        $pugo->setPostalCode('55555');
        $pugo->setLocality('SIN CITY 2');
        $pugo->setCountryCode('FR');

        $box->setPugoAddress($pugo);

        $customs = new CustomsInfo();
        $customs->setPrivateAddress(false);
        $customs->setParcelValue(9999);
        $customs->setContentDescription('SOME DESCRIPTION');
        $customs->setShipmentType(CustomsInfo::CUSTOM_INFO_SHIPMENT_TYPE_GOODS);
        $customs->setParcelReturnInstructions(CustomsInfo::CUSTOM_INFO_PARCEL_RETURN_INSTRUCTION_RTS);

        $box->setCustomsInfo($customs);

        $document = self::createDomDocument();
        $document->appendChild($box->toXml($document));
        $this->assertSame($this->getXmlToExpect(), $document->saveXML());
    }

    public function testCreateFromXML()
    {
        $box = AtIntlPugo::createFromXML(simplexml_load_string($this->getXml()));

        $this->assertSame('bpack@bpost international', $box->getProduct());
        $this->assertCount(2, $box->getOptions());

        $receiver = $box->getReceiver();

        $this->assertSame('SOME NAME', $receiver->getName());
        $this->assertSame('SOME COMPANY', $receiver->getCompany());
        $this->assertSame('123456789', $receiver->getPhoneNumber());
        $this->assertSame('someone@somedomain.be', $receiver->getEmailAddress());

        $address = $receiver->getAddress();

        $this->assertSame('SOME STREET', $address->getStreetName());
        $this->assertSame('999', $address->getNumber());
        $this->assertSame('99999', $address->getPostalCode());
        $this->assertSame('SIN CITY', $address->getLocality());
        $this->assertSame('FR', $address->getCountryCode());


        $this->assertSame(1234, $box->getParcelWeight());
        $this->assertSame('123', $box->getPugoId());
        $this->assertSame('SOME PUGO', $box->getPugoName());

        $pugo = $box->getPugoAddress();

        $this->assertSame('PUGO STREET', $pugo->getStreetName());
        $this->assertSame('555', $pugo->getNumber());
        $this->assertSame('55555', $pugo->getPostalCode());
        $this->assertSame('SIN CITY 2', $pugo->getLocality());
        $this->assertSame('FR', $pugo->getCountryCode());

        $customs = $box->getCustomsInfo();

        $this->assertSame(9999, $customs->getParcelValue());
        $this->assertSame('SOME DESCRIPTION', $customs->getContentDescription());
        $this->assertSame('GOODS', $customs->getShipmentType());
        $this->assertSame('RTS', $customs->getParcelReturnInstructions());
        $this->assertFalse($customs->getPrivateAddress());

    }

    private function getXml()
    {
        return <<<EOF
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<atIntlPugo xmlns="http://schema.post.be/shm/deepintegration/v3/"
        xmlns:ns2="http://schema.post.be/shm/deepintegration/v3/common"
        xmlns:ns4="http://schema.post.be/shm/deepintegration/v3/international">
    <ns4:product>bpack@bpost international</ns4:product>
    <ns4:options>
        <ns2:keepMeInformed language="NL">
            <ns2:emailAddress>someone@somedomain.be</ns2:emailAddress>
        </ns2:keepMeInformed>
        <ns2:signed/>
    </ns4:options>
    <ns4:receiver>
        <ns2:name>SOME NAME</ns2:name>
        <ns2:company>SOME COMPANY</ns2:company>
        <ns2:address>
            <ns2:streetName>SOME STREET</ns2:streetName>
            <ns2:number>999</ns2:number>
            <ns2:postalCode>99999</ns2:postalCode>
            <ns2:locality>SIN CITY</ns2:locality>
            <ns2:countryCode>FR</ns2:countryCode>
        </ns2:address>
        <ns2:emailAddress>someone@somedomain.be</ns2:emailAddress>
        <ns2:phoneNumber>123456789</ns2:phoneNumber>
    </ns4:receiver>
    <ns4:parcelWeight>1234</ns4:parcelWeight>
    <ns4:pugoId>123</ns4:pugoId>
    <ns4:pugoName>SOME PUGO</ns4:pugoName>
    <ns4:pugoAddress>
        <ns2:streetName>PUGO STREET</ns2:streetName>
        <ns2:number>555</ns2:number>
        <ns2:postalCode>55555</ns2:postalCode>
        <ns2:locality>SIN CITY 2</ns2:locality>
        <ns2:countryCode>FR</ns2:countryCode>
    </ns4:pugoAddress>
    <ns4:customsInfo>
        <ns4:parcelValue>9999</ns4:parcelValue>
        <ns4:contentDescription>SOME DESCRIPTION</ns4:contentDescription>
        <ns4:shipmentType>GOODS</ns4:shipmentType>
        <ns4:parcelReturnInstructions>RTS</ns4:parcelReturnInstructions>
        <ns4:privateAddress>false</ns4:privateAddress>
    </ns4:customsInfo>
</atIntlPugo>

EOF;

    }

    private function getXmlToExpect()
    {
        return <<<EOF
<?xml version="1.0" encoding="UTF-8"?>
<internationalBox>
  <international:atIntlPugo>
    <international:product>bpack@bpost international</international:product>
    <international:options>
      <common:keepMeInformed language="NL">
        <common:emailAddress>someone@somedomain.be</common:emailAddress>
      </common:keepMeInformed>
      <common:signed/>
    </international:options>
    <international:receiver>
      <common:name>SOME NAME</common:name>
      <common:company>SOME COMPANY</common:company>
      <common:address>
        <common:streetName>SOME STREET</common:streetName>
        <common:number>999</common:number>
        <common:postalCode>99999</common:postalCode>
        <common:locality>SIN CITY</common:locality>
        <common:countryCode>FR</common:countryCode>
      </common:address>
      <common:emailAddress>someone@somedomain.be</common:emailAddress>
      <common:phoneNumber>123456789</common:phoneNumber>
    </international:receiver>
    <international:parcelWeight>1234</international:parcelWeight>
    <international:customsInfo>
      <international:parcelValue>9999</international:parcelValue>
      <international:contentDescription>SOME DESCRIPTION</international:contentDescription>
      <international:shipmentType>GOODS</international:shipmentType>
      <international:parcelReturnInstructions>RTS</international:parcelReturnInstructions>
      <international:privateAddress>false</international:privateAddress>
    </international:customsInfo>
    <international:pugoId>123</international:pugoId>
    <international:pugoName>SOME PUGO</international:pugoName>
    <international:pugoAddress>
      <common:streetName>PUGO STREET</common:streetName>
      <common:number>555</common:number>
      <common:postalCode>55555</common:postalCode>
      <common:locality>SIN CITY 2</common:locality>
      <common:countryCode>FR</common:countryCode>
    </international:pugoAddress>
  </international:atIntlPugo>
</internationalBox>

EOF;
    }

}