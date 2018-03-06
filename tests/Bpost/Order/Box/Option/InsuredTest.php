<?php
namespace Bpost;

use Bpost\BpostApiClient\Bpost\Order\Box\Option\Insured;
use Bpost\BpostApiClient\Exception\BpostLogicException\BpostInvalidValueException;

class InsuredTest extends \PHPUnit_Framework_TestCase
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
     * Tests Insured->toXML
     */
    public function testToXML()
    {
        $self = new Insured();
        $self->setType(Insured::INSURANCE_TYPE_ADDITIONAL_INSURANCE);
        $self->setValue(Insured::INSURANCE_AMOUNT_UP_TO_2500_EUROS);

        // Without specific prefix
        $rootDom = $this->createDomDocument();
        $document = $this->generateDomDocument($rootDom, $self->toXML($rootDom));

        $this->assertSame($this->getXml(), $document->saveXML());

        // With specific prefix
        $rootDom = $this->createDomDocument();
        $document = $this->generateDomDocument($rootDom, $self->toXML($rootDom, 'mushroom'));

        $this->assertSame($this->getNamespaceXml(), $document->saveXML());
    }

    public function testCreateFromXml()
    {
        $insured = Insured::createFromXML(simplexml_load_string($this->getXml()));

        $this->assertSame(Insured::INSURANCE_TYPE_ADDITIONAL_INSURANCE, $insured->getType());
        $this->assertSame(Insured::INSURANCE_AMOUNT_UP_TO_2500_EUROS, $insured->getValue());
    }

    /**
     * Test validation in the setters
     */
    public function testFaultyProperties()
    {
        try {
            $insured = new Insured();
            $insured->setType(str_repeat('a', 10));
            $this->fail('BpostInvalidValueException not launched');
        } catch (BpostInvalidValueException $e) {
            // Nothing, the exception is good
        } catch (\Exception $e) {
            $this->fail('BpostInvalidValueException not caught');
        }

        try {
            $insured = new Insured();
            $insured->setType('additionalInsurance');
            $insured->setValue(str_repeat('1', 10));
            $this->fail('BpostInvalidValueException not launched');
        } catch (BpostInvalidValueException $e) {
            // Nothing, the exception is good
        } catch (\Exception $e) {
            $this->fail('BpostInvalidValueException not caught');
        }

        // Exceptions were caught,
        $this->assertTrue(true);
    }

    private function getXml()
    {
        return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<common:insured xmlns="http://schema.post.be/shm/deepintegration/v3/national" xmlns:common="http://schema.post.be/shm/deepintegration/v3/common" xmlns:tns="http://schema.post.be/shm/deepintegration/v3/" xmlns:international="http://schema.post.be/shm/deepintegration/v3/international" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://schema.post.be/shm/deepintegration/v3/">
  <common:additionalInsurance value="2"/>
</common:insured>

XML;
    }

    private function getNamespaceXml()
    {
        return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<mushroom:insured xmlns="http://schema.post.be/shm/deepintegration/v3/national" xmlns:common="http://schema.post.be/shm/deepintegration/v3/common" xmlns:tns="http://schema.post.be/shm/deepintegration/v3/" xmlns:international="http://schema.post.be/shm/deepintegration/v3/international" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://schema.post.be/shm/deepintegration/v3/">
  <mushroom:additionalInsurance value="2"/>
</mushroom:insured>

XML;
    }

}
