<?php

/*
 * This file is part of the DigiDoc package.
 *
 * (c) Kristen Gilden <kristen.gilden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KG\DigiDoc\tests;

use KG\DigiDoc\Envelope;

class EnvelopeTest extends \PHPUnit_Framework_TestCase
{
    public function testGetSignatureReturnsNullIfNoSuchId()
    {
        $envelope = new Envelope($this->getMockSession());

        $this->assertNull($envelope->getSignature('S01'));
    }

    public function testGetSignatureReturnsSignatureById()
    {
        $signature = $this->getMockSignature();
        $signature
            ->expects($this->once())
            ->method('getId')
            ->will($this->returnValue($signatureId = 'S01'))
        ;

        $envelope = new Envelope($this->getMockSession());
        $envelope->addSignature($signature);

        $this->assertSame($signature, $envelope->getSignature($signatureId));
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
}
