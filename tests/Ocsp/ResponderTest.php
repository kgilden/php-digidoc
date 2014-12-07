<?php

/*
 * This file is part of the DigiDoc package.
 *
 * (c) Kristen Gilden <kristen.gilden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KG\DigiDoc\Tests\Ocsp;

use KG\DigiDoc\Ocsp\Responder;
use org\bovigo\vfs\vfsStream;

class ResponderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException KG\DigiDoc\Exception\FileNotFoundException
     */
    public function testConstructFailsIfFileNotFound()
    {
        $responder = new Responder('http://example.com', 'does/not/exist');
    }

    public function testHandleGeneratesCorrectCommandLine()
    {
        $root = vfsStream::setUp();
        $pathToResponderCert = vfsStream::newFile('issuerCert.pem')->at($root)->url();

        $expectedCommandLine = sprintf(
            "?openssl ocsp -issuer '%s' -cert '%s' -url '%s' -VAfile '%s' -respout '.*'?",
            $pathToIssuerCert = 'path/to/issuer.pem',
            $pathToClientCert = 'path/to/client.pem',
            $url = 'http://example.com',
            $pathToResponderCert
        );

        $process = $this->getMockProcess();
        $process->method('isSuccessful')->willReturn(true);
        $process
            ->expects($this->once())
            ->method('setCommandLine')
            ->with($this->matchesRegularExpression($expectedCommandLine))
        ;


        $request = $this->getMockRequest();
        $request->method('getPathToClientCert')->willReturn($pathToClientCert);
        $request->method('getPathToIssuerCert')->willReturn($pathToIssuerCert);

        $responder = new Responder($url, $pathToResponderCert, vfsStream::url('root'), $process);
        $responder->handle($request);
    }

    /**
     * @expectedException KG\DigiDoc\Exception\OcspRequestException
     */
    public function testHandleFailsIfProcesssUnsuccessful()
    {
        $pathToResponderCert = vfsStream::newFile('issuerCert.pem')
            ->at(vfsStream::setUp())
            ->url()
        ;

        $process = $this->getMockProcess();
        $process->method('isSuccessful')->willReturn(false);

        $responder = new Responder('http://example.com', $pathToResponderCert, null, $process);
        $responder->handle($this->getMockRequest());
    }

    public function testHandleReturnsResponseIfSuccessful()
    {
        $pathToResponderCert = vfsStream::newFile('issuerCert.pem')
            ->at(vfsStream::setUp())
            ->url()
        ;

        $process = $this->getMockProcess();
        $process->method('isSuccessful')->willReturn(true);

        $responder = new Responder('http://example.com', $pathToResponderCert, null, $process);
        $response = $responder->handle($this->getMockRequest());

        $this->assertInstanceOf('KG\DigiDoc\OCSP\Response', $response);
    }

    private function getMockProcess()
    {
        return $this
            ->getMockBuilder('Symfony\Component\Process\Process')
            ->disableOriginalConstructor()
            ->getMock()
        ;
    }

    private function getMockRequest()
    {
        return $this
            ->getMockBuilder('KG\DigiDoc\Ocsp\Request')
            ->disableOriginalConstructor()
            ->getMock()
        ;
    }
}
