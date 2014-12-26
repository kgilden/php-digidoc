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

use KG\DigiDoc\Ocsp\Asn1;
use KG\DigiDoc\Ocsp\Responder;
use org\bovigo\vfs\vfsStream;
use phpseclib\File\ASN1 as Asn1Parser;

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

        try {
            $responder->handle($request);
        } catch (\Exception $e) {
            // Ignore exceptions thrown by Response.
        }
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
        $process->method('setCommandLine')->will($this->returnCallback(function ($commandLine) {
            // NB! This assumes the outfile is the last argument. If things go
            // south, assume somone has fiddled with the argument order.
            $commandLine = explode('-respout', $commandLine);
            $fileName = trim(end($commandLine), '\' ');

            $parser = new Asn1Parser();
            $asn1 = new Asn1();

            file_put_contents($fileName, $parser->encodeDER(array(
                'responseStatus' => Asn1::OCSP_SUCCESSFUL,
                'responseBytes' => array(
                    'responseType' => Asn1::OID_ID_PKIX_OCSP_BASIC,
                    'response' => base64_encode('Hello, world!'),
                ),
            ), $asn1->OCSPResponse));
        }));

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
