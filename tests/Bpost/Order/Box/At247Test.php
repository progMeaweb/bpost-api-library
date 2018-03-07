<?php
namespace Bpost;

use Bpost\BpostApiClient\Bpost\Order\Box\At247;
use Bpost\BpostApiClient\Bpost\Order\Box\National\UnregisteredParcelLockerMember;
use Bpost\BpostApiClient\Bpost\Order\ParcelsDepotAddress;
use Bpost\BpostApiClient\Common\BasicAttribute\Language;
use Bpost\BpostApiClient\Exception\BpostLogicException\BpostInvalidValueException;

class At247Test extends \PHPUnit_Framework_TestCase
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
     * Tests At247->toXML
     *
     * @warning
     * That is a bad test, we cannot have a memberId AND an unregisteredParcelLockerMember
     * We must to have a XML with memberId, another one with unregisteredParcelLockerMember and another one without (to see comportment)
     */
    public function testToXML()
    {
        $data = array(
            'at24-7' => array(
                'product' => 'bpack 24h Pro',
                'weight' => 2000,
                'parcelsDepotId' => '014472',
                'parcelsDepotName' => 'WIJNEGEM',
                'parcelsDepotAddress' => array(
                    'streetName' => 'Turnhoutsebaan',
                    'number' => '468',
                    'box' => 'A',
                    'postalCode' => '2110',
                    'locality' => 'Wijnegem',
                    'countryCode' => 'BE',
                ),
                'memberId' => '188565346',
                'unregistered' => array(
                    'language' => 'EN',
                    'mobilePhone' => '0471000000',
                    'emailAddress' => 'pomme@antidot.com'
                ), // Bad test: We cannot have a memberId AND an unregisteredParcelLockerMember
                'receiverName' => 'Tijs Verkoyen',
                'receiverCompany' => 'Sumo Coders',
                'requestedDeliveryDate' => '2016-03-16',
            ),
        );

        $expectedDocument = self::createDomDocument();
        $nationalBox = $expectedDocument->createElement('nationalBox');
        $at247 = $expectedDocument->createElement('at24-7');
        $nationalBox->appendChild($at247);
        $expectedDocument->appendChild($nationalBox);
        foreach ($data['at24-7'] as $key => $value) {
            if ($key == 'parcelsDepotAddress') {
                $address = $expectedDocument->createElement($key);
                foreach ($value as $key2 => $value2) {
                    $key2 = 'common:' . $key2;
                    $address->appendChild(
                        $expectedDocument->createElement($key2, $value2)
                    );
                }
                $at247->appendChild($address);
            } elseif ($key == 'unregistered') {
                $child = $expectedDocument->createElement($key);
                foreach ($value as $key2 => $value2) {
                    $child->appendChild(
                        $expectedDocument->createElement($key2, $value2)
                    );
                }
                $at247->appendChild($child);
            } else {
                $at247->appendChild(
                    $expectedDocument->createElement($key, $value)
                );
            }
        }

        $actualDocument = self::createDomDocument();
        $parcelsDepotAddress = new ParcelsDepotAddress(
            $data['at24-7']['parcelsDepotAddress']['streetName'],
            $data['at24-7']['parcelsDepotAddress']['number'],
            $data['at24-7']['parcelsDepotAddress']['box'],
            $data['at24-7']['parcelsDepotAddress']['postalCode'],
            $data['at24-7']['parcelsDepotAddress']['locality'],
            $data['at24-7']['parcelsDepotAddress']['countryCode']
        );
        $unregisteredParcelLockerMember = new UnregisteredParcelLockerMember();
        $unregisteredParcelLockerMember->setLanguage($data['at24-7']['unregistered']['language']);
        $unregisteredParcelLockerMember->setMobilePhone($data['at24-7']['unregistered']['mobilePhone']);
        $unregisteredParcelLockerMember->setEmailAddress($data['at24-7']['unregistered']['emailAddress']);

        $at247 = new At247();
        $at247->setProduct($data['at24-7']['product']);
        $at247->setWeight($data['at24-7']['weight']);
        $at247->setRequestedDeliveryDate($data['at24-7']['requestedDeliveryDate']);
        $at247->setParcelsDepotId($data['at24-7']['parcelsDepotId']);
        $at247->setParcelsDepotName($data['at24-7']['parcelsDepotName']);
        $at247->setParcelsDepotAddress($parcelsDepotAddress);
        $at247->setMemberId($data['at24-7']['memberId']);
        $at247->setUnregisteredParcelLockerMember($unregisteredParcelLockerMember);
        $at247->setReceiverName($data['at24-7']['receiverName']);
        $at247->setReceiverCompany($data['at24-7']['receiverCompany']);
        $actualDocument->appendChild(
            $at247->toXML($actualDocument)
        );

        $this->assertSame($expectedDocument->saveXML(), $actualDocument->saveXML());
    }

    public function testCreateFromXml(){
        $at247 = At247::createFromXML(simplexml_load_string($this->getXml()));

        $this->assertSame('bpack 24/7', $at247->getProduct());
        $this->assertCount(2, $at247->getOptions());
        $this->assertSame(1234, $at247->getWeight());

        $this->assertSame('RECEIVER NAME', $at247->getReceiverName());

        $this->assertSame('99999', $at247->getParcelsDepotId());
        $this->assertSame('SOME DEPOT', $at247->getParcelsDepotName());
        $parcel = $at247->getParcelsDepotAddress();

        $this->assertSame('DEPOT STREET', $parcel->getStreetName());
        $this->assertSame('1111', $parcel->getNumber());
        $this->assertSame('9999', $parcel->getPostalCode());
        $this->assertSame('SIN CITY', $parcel->getLocality());
        $this->assertSame('BE', $parcel->getCountryCode());

        $member = $at247->getUnregisteredParcelLockerMember();

        $this->assertSame('NL', $member->getLanguage());
        $this->assertSame('123456789', $member->getMobilePhone());
        $this->assertSame('someone@somedomain.be', $member->getEmailAddress());

    }

    /**
     * Test validation in the setters
     */
    public function testFaultyProperties()
    {
        $at247 = new At247();

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

    private function getXml()
    {
        return <<<EOF
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<at24-7 xmlns="http://schema.post.be/shm/deepintegration/v3/"
        xmlns:ns2="http://schema.post.be/shm/deepintegration/v3/common"
        xmlns:ns3="http://schema.post.be/shm/deepintegration/v3/national">
    <ns3:product>bpack 24/7</ns3:product>
    <ns3:weight>1234</ns3:weight>
    <ns3:openingHours/>
    <ns3:parcelsDepotId>99999</ns3:parcelsDepotId>
    <ns3:parcelsDepotName>SOME DEPOT</ns3:parcelsDepotName>
    <ns3:parcelsDepotAddress>
        <ns2:streetName>DEPOT STREET</ns2:streetName>
        <ns2:number>1111</ns2:number>
        <ns2:postalCode>9999</ns2:postalCode>
        <ns2:locality>SIN CITY</ns2:locality>
        <ns2:countryCode>BE</ns2:countryCode>
    </ns3:parcelsDepotAddress>
    <ns3:options>
        <ns2:insured>
            <ns2:additionalInsurance value="3"/>
        </ns2:insured>
        <ns2:signed/>
    </ns3:options>
    <ns3:unregistered>
        <ns3:language>NL</ns3:language>
        <ns3:mobilePhone>123456789</ns3:mobilePhone>
        <ns3:emailAddress>someone@somedomain.be</ns3:emailAddress>
    </ns3:unregistered>
    <ns3:receiverName>RECEIVER NAME</ns3:receiverName>
</at24-7>
EOF;

    }
}
