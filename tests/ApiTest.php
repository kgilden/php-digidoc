<?php

/*
 * This file is part of the DigiDoc package.
 *
 * (c) Kristen Gilden <kristen.gilden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KG\DigiDoc\Tests\ApiTest;

use KG\DigiDoc\Api;

class ApiTest extends \PHPUnit_Framework_TestCase
{
    public function testCreateCreatesNewArchive()
    {
        $client = $this->getMockClient();
        $this->mockStartSession($client, $sessionId = 42);

        $client
            ->expects($this->at(1))
            ->method('__soapCall')
            ->with('CreateSignedDoc')
        ;

        $api = new Api($client, $this->getMockEncoder(), $this->getMockTracker());

        $archive = $api->create();

        $this->assertInstanceOf('KG\DigiDoc\Archive', $archive);
        $this->assertEquals($sessionId, $archive->getSession()->getId());
    }

    public function testOpenCreatesNewArchive()
    {
        $info = new \stdClass();
        $info->DataFileInfo = $info->SignatureInfo = null;

        $client = $this->getMockClient();

        $client
            ->expects($this->once())
            ->method('__soapCall')
            ->with('StartSession', $this->anything())
            ->will($this->returnValue([
                'Status'        => 'OK',
                'Sesscode'      => $sessionId = 42,
                'SignedDocInfo' => $info,
            ]))
        ;

        $api = new Api($client, $this->getMockEncoder(), $this->getMockTracker());

        $archive = $api->open('/path/to/file.bdoc');

        $this->assertInstanceOf('KG\DigiDoc\Archive', $archive);
        $this->assertEquals($sessionId, $archive->getSession()->getId());
    }

    public function testOpenAddsFilesToArchive()
    {
        $fileInfo = $this->getMockDataFileInfo();
        $fileInfo->Id = 'example.doc';

        $info = new \stdClass();
        $info->DataFileInfo = $fileInfo;
        $info->SignatureInfo = null;

        $client = $this->getMockClient();

        $client
            ->expects($this->once())
            ->method('__soapCall')
            ->with('StartSession', $this->anything())
            ->will($this->returnValue([
                'Status'        => 'OK',
                'Sesscode'      => $sessionId = 42,
                'SignedDocInfo' => $info,
            ]))
        ;

        $api = new Api($client, $this->getMockEncoder(), $this->getMockTracker());

        $archive = $api->open('/path/to/file.bdoc');
        $file = $archive->getFiles()->first();

        $this->assertSame($fileInfo->Id, $file->getId());
    }

    public function testOpenAddsSignaturesToArchive()
    {
        $signatureInfo = $this->getMockSignatureInfo();
        $signatureInfo->Id = 'S0';

        $info = new \stdClass();
        $info->DataFileInfo = null;
        $info->SignatureInfo = $signatureInfo;

        $client = $this->getMockClient();

        $client
            ->expects($this->once())
            ->method('__soapCall')
            ->with('StartSession', $this->anything())
            ->will($this->returnValue([
                'Status'        => 'OK',
                'Sesscode'      => $sessionId = 42,
                'SignedDocInfo' => $info,
            ]))
        ;

        $api = new Api($client, $this->getMockEncoder(), $this->getMockTracker());

        $archive = $api->open('/path/to/file.bdoc');
        $signature = $archive->getSignatures()->first();

        $this->assertSame($signatureInfo->Id, $signature->getId());
    }

    public function testCloseClosesSession()
    {
        $session = $this->getMockSession();

        $session
            ->expects($this->once())
            ->method('getId')
            ->will($this->returnValue($sessionId = 69))
        ;

        $archive = $this->getMockArchive();

        $archive
            ->expects($this->once())
            ->method('getSession')
            ->will($this->returnValue($session))
        ;

        $client = $this->getMockClient();

        $client
            ->expects($this->once())
            ->method('__soapCall')
            ->with('CloseSession', [$sessionId])
        ;

        $api = new Api($client, $this->getMockEncoder(), $this->getMockTracker());
        $api->close($archive);
    }

    /**
     * @return \KG\DigiDoc\Archive|PHPUnit_Framework_MockObject_MockObject
     */
    private function getMockArchive()
    {
        return $this
            ->getMockBuilder('KG\DigiDoc\Archive')
            ->disableOriginalConstructor()
            ->getMock()
        ;
    }

    /**
     * @return \KG\DigiDoc\Certificate|PHPUnit_Framework_MockObject_MockObject
     */
    private function getMockCertificate()
    {
        return $this
            ->getMockBuilder('KG\DigiDoc\Certificate')
            ->disableOriginalConstructor()
            ->getMock()
        ;
    }

    /**
     * @return \SoapClient|PHPUnit_Framework_MockObject_MockObject
     */
    private function getMockClient()
    {
        return $this
            ->getMockBuilder('SoapClient')
            ->disableOriginalConstructor()
            ->getMock()
        ;
    }

    /**
     * @return \KG\DigiDoc\Soap\Wsdl\DataFileInfo|PHPUnit_Framework_MockObject_MockObject
     */
    private function getMockDataFileInfo()
    {
        return $this
            ->getMockBuilder('KG\DigiDoc\Soap\Wsdl\DataFileInfo')
            ->disableOriginalConstructor()
            ->getMock()
        ;
    }

    /**
     * @return \KG\DigiDoc\Encoder|PHPUnit_Framework_MockObject_MockObject
     */
    private function getMockEncoder()
    {
        return $this->getMock('KG\DigiDoc\Encoder');
    }

    /**
     * @return \KG\DigiDoc\Session|PHPUnit_Framework_MockObject_MockObject
     */
    private function getMockSession()
    {
        return $this
            ->getMockBuilder('KG\DigiDoc\Session')
            ->disableOriginalConstructor()
            ->getMock()
        ;
    }

    /**
     * @return \KG\DigiDoc\Signature|PHPUnit_Framework_MockObject_MockObject
     */
    private function getMockSignature()
    {
        return $this
            ->getMockBuilder('KG\DigiDoc\Signature')
            ->disableOriginalConstructor()
            ->getMock()
        ;
    }

    /**
     * @return \KG\DigiDoc\Soap\Wsdl\SignatureInfo|PHPUnit_Framework_MockObject_MockObject
     */
    private function getMockSignatureInfo()
    {
        return $this
            ->getMockBuilder('KG\DigiDoc\Soap\Wsdl\SignatureInfo')
            ->disableOriginalConstructor()
            ->getMock()
        ;
    }

    /**
     * @return \KG\DigiDoc\Tracker|PHPUnit_Framework_MockObject_MockObject
     */
    private function getMockTracker()
    {
        return $this->getMock('KG\DigiDoc\Tracker');
    }

    /**
     * Mocks the start session soap call.
     */
    private function mockStartSession(\PHPUnit_Framework_MockObject_MockObject $client, $sessionId)
    {
        $client
            ->expects($this->at(0))
            ->method('__soapCall')
            ->with('StartSession')
            ->will($this->returnValue(['Sesscode' => $sessionId]))
        ;
    }
}
