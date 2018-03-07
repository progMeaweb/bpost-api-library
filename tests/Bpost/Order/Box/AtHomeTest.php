<?php
namespace Bpost;

use Bpost\BpostApiClient\Bpost\Order\Address;
use Bpost\BpostApiClient\Bpost\Order\Box\AtHome;
use Bpost\BpostApiClient\Bpost\Order\Box\Option\Messaging;
use Bpost\BpostApiClient\Bpost\Order\Box\Option\Signed;
use Bpost\BpostApiClient\Bpost\Order\Receiver;
use Bpost\BpostApiClient\Exception\BpostLogicException\BpostInvalidValueException;

class AtHomeTest extends \PHPUnit_Framework_TestCase
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

    /**
     * @param \DOMDocument $document
     * @param \DOMElement  $element
     * @return \DOMDocument
     */
    private function generateDomDocument(\DOMDocument $document, \DOMElement $element)
    {
        $element->setAttribute(
            'xmlns:common',
            'http://schema.post.be/shm/deepintegration/v3/common'
        );
        $element->setAttribute(
            'xmlns:tns',
            'http://schema.post.be/shm/deepintegration/v3/'
        );
        $element->setAttribute(
            'xmlns',
            'http://schema.post.be/shm/deepintegration/v3/national'
        );
        $element->setAttribute(
            'xmlns:international',
            'http://schema.post.be/shm/deepintegration/v3/international'
        );
        $element->setAttribute(
            'xmlns:xsi',
            'http://www.w3.org/2001/XMLSchema-instance'
        );
        $element->setAttribute(
            'xsi:schemaLocation',
            'http://schema.post.be/shm/deepintegration/v3/'
        );

        $document->appendChild($element);

        return $document;
    }

    /**
     * Tests Address->toXML
     */
    public function testToXML()
    {
        $address = new Address();
        $address->setCountryCode('BE');
        $address->setPostalCode('9999');
        $address->setLocality('SIN CITY');
        $address->setStreetName('SOME STREET');
        $address->setNumber('999');

        $receiver = new Receiver();
        $receiver->setName('SOME NAME');
        $receiver->setEmailAddress('someone@somedomain.be');
        $receiver->setCompany('SOME COMPANY');
        $receiver->setAddress($address);
        $receiver->setPhoneNumber('123456789');

        $self = new AtHome();
        $self->setProduct('bpack 24h Pro');
        $self->setRequestedDeliveryDate('2016-03-16');
        $self->setReceiver($receiver);

        $keepMeInformed = new Messaging(Messaging::MESSAGING_TYPE_KEEP_ME_INFORMED, 'NL');
        $self->addOption($keepMeInformed);
        $self->addOption(new Signed());

        // Normal
        $rootDom = $this->createDomDocument();
        $document = $this->generateDomDocument($rootDom, $self->toXML($rootDom, 'tns'));

        $this->assertSame($this->getXml(), $document->saveXML());
    }

    public function testCreateFromNormalXml() {

        $self = AtHome::createFromXML(new \SimpleXMLElement($this->getXml()));

        $this->assertSame('bpack 24h Pro', $self->getProduct());
        $this->assertCount(2, $self->getOptions());

        $receiver = $self->getReceiver();
        $this->assertSame('SOME NAME', $receiver->getName());
        $this->assertSame('SOME COMPANY', $receiver->getCompany());
        $this->assertSame('someone@somedomain.be', $receiver->getEmailAddress());
        $this->assertSame('123456789', $receiver->getPhoneNumber());

        $address = $receiver->getAddress();
        $this->assertSame('SOME STREET', $address->getStreetName());
        $this->assertSame('999', $address->getNumber());
        $this->assertSame('9999', $address->getPostalCode());
        $this->assertSame('SIN CITY', $address->getLocality());
        $this->assertSame('BE', $address->getCountryCode());


        $this->assertSame('2016-03-16', $self->getRequestedDeliveryDate());
    }

    public function testCreateFromBadXml() {
        $this->expectException('Bpost\BpostApiClient\Exception\XmlException\BpostXmlInvalidItemException');
        AtHome::createFromXML(new \SimpleXMLElement($this->getNotAtHomeXml()));
    }

    /**
     * Test validation in the setters
     */
    public function testFaultyProperties()
    {
        $atHome = new AtHome();

        try {
            $atHome->setProduct(str_repeat('a', 10));
            $this->fail('BpostInvalidValueException not launched');
        } catch (BpostInvalidValueException $e) {
            // Nothing, the exception is good
        } catch (\Exception $e) {
            $this->fail('BpostInvalidValueException not caught');
        }

        // Exceptions were caught,
        $this->assertTrue(true);
    }

    private function getXml() {
        return <<<EOF
<?xml version="1.0" encoding="UTF-8"?>
<tns:nationalBox xmlns="http://schema.post.be/shm/deepintegration/v3/national" xmlns:common="http://schema.post.be/shm/deepintegration/v3/common" xmlns:tns="http://schema.post.be/shm/deepintegration/v3/" xmlns:international="http://schema.post.be/shm/deepintegration/v3/international" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://schema.post.be/shm/deepintegration/v3/">
  <atHome>
    <product>bpack 24h Pro</product>
    <options>
      <common:keepMeInformed language="NL"/>
      <common:signed/>
    </options>
    <receiver>
      <common:name>SOME NAME</common:name>
      <common:company>SOME COMPANY</common:company>
      <common:address>
        <common:streetName>SOME STREET</common:streetName>
        <common:number>999</common:number>
        <common:postalCode>9999</common:postalCode>
        <common:locality>SIN CITY</common:locality>
        <common:countryCode>BE</common:countryCode>
      </common:address>
      <common:emailAddress>someone@somedomain.be</common:emailAddress>
      <common:phoneNumber>123456789</common:phoneNumber>
    </receiver>
    <requestedDeliveryDate>2016-03-16</requestedDeliveryDate>
  </atHome>
</tns:nationalBox>

EOF;
    }

    private function getNotAtHomeXml() {
        return <<<EOF
<?xml version="1.0" encoding="UTF-8"?>
<tns:nationalBox xmlns="http://schema.post.be/shm/deepintegration/v3/national" xmlns:common="http://schema.post.be/shm/deepintegration/v3/common" xmlns:tns="http://schema.post.be/shm/deepintegration/v3/" xmlns:international="http://schema.post.be/shm/deepintegration/v3/international" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://schema.post.be/shm/deepintegration/v3/">
  <notAtHome>
    <product>bpack 24h Pro</product>
  </notAtHome>
</tns:nationalBox>

EOF;
    }
}
