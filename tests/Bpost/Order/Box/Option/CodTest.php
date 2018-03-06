<?php
namespace Bpost;

use Bpost\BpostApiClient\Bpost\Order\Box\Option\Cod;

class CodTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Create a generic DOM Document
     *
     * @return \DOMDocument
     */
    private static function createDomDocument()
    {
        $document = new \DOMDocument('1.0', 'utf-8');
        $document->preserveWhiteSpace = false;
        $document->formatOutput = true;

        return $document;
    }

    /**
     * Tests Cod->toXML
     */
    public function testToXML()
    {
        $data = array(
            'cod' => array(
                'codAmount' => 1251,
                'iban' => 'BE19210023508812',
                'bic' => 'GEBABEBB',
            ),
        );

        $expectedDocument = self::createDomDocument();
        $cod = $expectedDocument->createElement('common:cod');
        foreach ($data['cod'] as $key => $value) {
            $cod->appendChild(
                $expectedDocument->createElement('common:'.$key, $value)
            );
        }
        $expectedDocument->appendChild($cod);

        $actualDocument = self::createDomDocument();
        $cashOnDelivery = new Cod();
        $cashOnDelivery->setAmount($data['cod']['codAmount']);
        $cashOnDelivery->setIban($data['cod']['iban']);
        $cashOnDelivery->setBic($data['cod']['bic']);

        $actualDocument->appendChild(
            $cashOnDelivery->toXML($actualDocument)
        );

        $this->assertEquals($expectedDocument->saveXML(), $actualDocument->saveXML());

        $data = array(
            'cod' => array(
                'codAmount' => 1251,
                'iban' => 'BE19210023508812',
                'bic' => 'GEBABEBB',
            ),
        );

        $expectedDocument = self::createDomDocument();
        $cod = $expectedDocument->createElement('foo:cod');
        foreach ($data['cod'] as $key => $value) {
            $cod->appendChild(
                $expectedDocument->createElement('foo:'. $key, $value)
            );
        }
        $expectedDocument->appendChild($cod);

        $actualDocument = self::createDomDocument();
        $cashOnDelivery = new Cod();
        $cashOnDelivery->setAmount($data['cod']['codAmount']);
        $cashOnDelivery->setIban($data['cod']['iban']);
        $cashOnDelivery->setBic($data['cod']['bic']);
        
        $actualDocument->appendChild(
            $cashOnDelivery->toXML($actualDocument, 'foo')
        );

        $this->assertSame($expectedDocument->saveXML(), $actualDocument->saveXML());
    }


    public function testCreateFromXML(){
        $cod = Cod::createFromXML(simplexml_load_string($this->getXml()));

        $this->assertSame(1234, $cod->getAmount());
        $this->assertSame("SOMEIBAN", $cod->getIban());
        $this->assertSame("SOMEBIC", $cod->getBic());
    }

    private function getXml()
    {
        return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<common:cod xmlns="http://schema.post.be/shm/deepintegration/v3/national" xmlns:common="http://schema.post.be/shm/deepintegration/v3/common" xmlns:tns="http://schema.post.be/shm/deepintegration/v3/" xmlns:international="http://schema.post.be/shm/deepintegration/v3/international" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://schema.post.be/shm/deepintegration/v3/">
  <common:codAmount>1234</common:codAmount>
  <common:iban>SOMEIBAN</common:iban>
  <common:bic>SOMEBIC</common:bic>
</common:cod>

XML;
    }
}
