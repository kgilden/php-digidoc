<?php

/*
 * This file is part of the DigiDoc package.
 *
 * (c) Kristen Gilden <kristen.gilden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KG\DigiDoc\Tests;

use KG\DigiDoc\Signature;
use KG\DigiDoc\Soap\Wsdl\SignatureInfo;

class SignatureTest extends \PHPUnit_Framework_TestCase
{
    public function testSignatureCreatedFromSoapIsSealed()
    {
        $signature = Signature::createFromSoap($this->getMockSignatureInfo());

        $this->assertTrue($signature->isSealed());
    }

    public function testSignatureCreatedFromSoapHasId()
    {
        $info = $this->getMockSignatureInfo();
        $info->Id = 'S1';

        $signature = Signature::createFromSoap($info);

        $this->assertEquals($info->Id, $signature->getId());
    }

    public function testSignatureUnsealedByDefault()
    {
        $signature = new Signature($this->getMockCertificate());

        $this->assertFalse($signature->isSealed());
    }

    public function testSealSealsSignature()
    {
        $signature = new Signature($this->getMockCertificate());
        $signature->seal();

        $this->assertTrue($signature->isSealed());
    }

    private function getMockCertificate()
    {
        return $this
            ->getMockBuilder('KG\DigiDoc\Certificate')
            ->disableOriginalConstructor()
            ->getMock()
        ;
    }

    private function getMockSignatureInfo()
    {
        return $this
            ->getMockBuilder('KG\DigiDoc\Soap\Wsdl\SignatureInfo')
            ->disableOriginalConstructor()
            ->getMock()
        ;
    }
}
