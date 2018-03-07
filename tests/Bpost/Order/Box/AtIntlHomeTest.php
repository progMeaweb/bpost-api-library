<?php

namespace Bpost;


use Bpost\BpostApiClient\Bpost\Order\Address;
use Bpost\BpostApiClient\Bpost\Order\Box\AtIntlHome;
use Bpost\BpostApiClient\Bpost\Order\Box\Option\AutomaticSecondPresentation;
use Bpost\BpostApiClient\Bpost\Order\Box\Option\Insured;
use Bpost\BpostApiClient\Bpost\Order\Receiver;

class AtIntlHomeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Create a generic DOM Document
     *
     * @return \DOMDocument
     */
    private function createDomDocument()
    {
        $document = new \DOMDocument('1.0', 'UTF-8');
        $document->preserveWhiteSpace = false;
        $document->formatOutput = true;

        return $document;
    }

    public function testToXml()
    {
        $box = new AtIntlHome();

        $box->setProduct('bpack Europe Business');

        $box->addOption(new AutomaticSecondPresentation());
        $insured = new Insured();
        $insured->setType(Insured::INSURANCE_TYPE_ADDITIONAL_INSURANCE);
        $insured->setValue(2);
        $box->addOption($insured);

        $box->setParcelWeight(1234);

        $receiver = new Receiver();
        $receiver->setName('SOME NAME');
        $receiver->setCompany('SOME COMPANY');
        $receiver->setPhoneNumber('123456789');
        $receiver->setEmailAddress('someone@somedomain.be');

        $address = new Address();
        $address->setStreetName('SOME STREET');
        $address->setNumber('999');
        $address->setPostalCode('99999');
        $address->setLocality('SIN CITY');
        $address->setCountryCode('FR');
        $receiver->setAddress($address);

        $box->setReceiver($receiver);


        $document = self::createDomDocument();
        $document->appendChild($box->toXml($document));

        $this->assertSame($this->xmlToExpect(), $document->saveXML());
    }

    public function testCreateFromXML()
    {
        $box = AtIntlHome::createFromXML(simplexml_load_string($this->getXml()));

        $this->assertSame('bpack Europe Business', $box->getProduct());
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

    }

    private function getXml()
    {
        return <<<EOF
<?xml version="1.0" encoding="UTF-8"?>
<atIntlHome xmlns="http://schema.post.be/shm/deepintegration/v3/"
        xmlns:ns2="http://schema.post.be/shm/deepintegration/v3/common"
        xmlns:ns4="http://schema.post.be/shm/deepintegration/v3/international">
    <ns4:product>bpack Europe Business</ns4:product>
    <ns4:options>
        <ns2:automaticSecondPresentation/>
        <ns2:insured>
            <ns2:additionalInsurance value="2"/>
        </ns2:insured>
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
</atIntlHome>

EOF;

    }

    private function xmlToExpect()
    {
        return <<<EOF
<?xml version="1.0" encoding="UTF-8"?>
<internationalBox>
  <international:atIntlHome>
    <international:product>bpack Europe Business</international:product>
    <international:options>
      <common:automaticSecondPresentation/>
      <common:insured>
        <common:additionalInsurance value="2"/>
      </common:insured>
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
  </international:atIntlHome>
</internationalBox>

EOF;

    }


}