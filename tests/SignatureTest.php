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

class SignatureTest extends \PHPUnit_Framework_TestCase
{
    public function testNewSignatureIsUnsealed()
    {
        $signature = new Signature(
            $this->getMockApi(),
            $this->getMockCertificate(),
            'F005B411',
            'B45EBA11'
        );

        $this->assertFalse($signature->isSealed());
    }

    public function testCorrectSolutionSealsSignature()
    {
        $api = $this->getMockApi();
        $api
            ->expects($this->once())
            ->method('finishSignature')
            ->will($this->returnValue(true))
        ;

        $signature = new Signature($api, $this->getMockCertificate(), 'F005B411', 'B45EBA11');
        $signature->seal('DEADBEEF');

        $this->assertTrue($signature->isSealed());
    }

    public function testIncorrectSolutionKeepsSignatureUnsealed()
    {
        $api = $this->getMockApi();
        $api
            ->expects($this->once())
            ->method('finishSignature')
            ->will($this->returnValue(false))
        ;

        $signature = new Signature($api, $this->getMockCertificate(), 'F005B411', 'B45EBA11');
        $signature->seal('DEADBEEF');

        $this->assertFalse($signature->isSealed());
    }

    /**
     * @return \KG\DigiDoc\Api|PHPUnit_Framework_MockObject_MockObject
     */
    private function getMockApi()
    {
        return $this
            ->getMockBuilder('KG\DigiDoc\Api')
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
}
