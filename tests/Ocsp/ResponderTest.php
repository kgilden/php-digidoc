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

    /**
     * @todo This can be simplified once Responses are created by a factory.
     */
    public function testHandleReturnsResponseIfSuccessful()
    {
        $pathToResponderCert = vfsStream::newFile('issuerCert.pem')
            ->at(vfsStream::setUp())
            ->withContent($this->getCertInPem())
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
                    'response' => 'MIICOTCCASGhgYYwgYMxCzAJBgNVBAYTAkVFMSIwIAYDVQQKDBlBUyBTZXJ0aWZpdHNlZXJpbWlza2Vza3VzMQ0wCwYDVQQLDARPQ1NQMScwJQYDVQQDDB5URVNUIG9mIFNLIE9DU1AgUkVTUE9OREVSIDIwMTExGDAWBgkqhkiG9w0BCQEWCXBraUBzay5lZRgPMjAxNDEyMjYyMzE1NDVaMGAwXjBJMAkGBSsOAwIaBQAEFJ8hzI+QiAAqq1ikY3MvViFZKzWuBBR7avJVUFy42XoIh0Gu+qIrPVtXdgIQH/v/rqwJX11SX33gZ4PrfYAAGA8yMDE0MTIyNjIzMTU0NVqhIzAhMB8GCSsGAQUFBzABAgQSBBDXw6pZv+/fMYQlxV3ACvKZMA0GCSqGSIb3DQEBBQUAA4IBAQBxe4hdQYCqR+O5wLFP1nY5HiP4w348YXfFiEvVmC9JCoaoSqmXdoner0sJxYdnOleu7/WdRAvO+hAnl73aOm0l+woGpm1fud8pl7Bz0F8cIiYL4g5xorArkdHZLwMmxi09ZzhBgM93xyOtpUj1c2onIXLEyV4ENv6DPBIAPNOVVTiaeFBVGba7g4RZxgvHWeuO+OmCAezjYJNZfXaYshvudAxaqmrhBCd3xDAYjgQlarhRn6aXpNsVRZG8NK4XW6+rH+4q+9S2ZsA6KTVkfGC218unYUkA0FswJH1JO7D+G9kooZHGIuV7SL5l4bpGwNxcbtdu+xYtNqNr4xSkHBTn',
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

    private function getCertInPem()
    {
        return <<<CERT
-----BEGIN CERTIFICATE-----
MIIEijCCA3KgAwIBAgIQaI8x6BnacYdNdNwlYnn/mzANBgkqhkiG9w0BAQUFADB9
MQswCQYDVQQGEwJFRTEiMCAGA1UECgwZQVMgU2VydGlmaXRzZWVyaW1pc2tlc2t1
czEwMC4GA1UEAwwnVEVTVCBvZiBFRSBDZXJ0aWZpY2F0aW9uIENlbnRyZSBSb290
IENBMRgwFgYJKoZIhvcNAQkBFglwa2lAc2suZWUwHhcNMTEwMzA3MTMyMjQ1WhcN
MjQwOTA3MTIyMjQ1WjCBgzELMAkGA1UEBhMCRUUxIjAgBgNVBAoMGUFTIFNlcnRp
Zml0c2VlcmltaXNrZXNrdXMxDTALBgNVBAsMBE9DU1AxJzAlBgNVBAMMHlRFU1Qg
b2YgU0sgT0NTUCBSRVNQT05ERVIgMjAxMTEYMBYGCSqGSIb3DQEJARYJcGtpQHNr
LmVlMIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA0cw6Cja17BbYbHi6
frwccDI4BIQLk/fiCE8L45os0xhPgEGR+EHE8LPCIqofPgf4gwN1vDE6cQNUlK0O
d+Ush39i9Z45esnfpGq+2HsDJaFmFr5+uC1MEz5Kn1TazEvKbRjkGnSQ9BertlGe
r2BlU/kqOk5qA5RtJfhT0psc1ixKdPipv59wnf+nHx1+T+fPWndXVZLoDg4t3w8l
IvIE/KhOSMlErvBIHIAKV7yH1hOxyeGLghqzMiAn3UeTEOgoOS9URv0C/T5C3mH+
Y/uakMSxjNuz41PneimCzbEJZJRiEaMIj8qPAubcbL8GtY03MWmfNtX6/wh6u6TM
fW8S2wIDAQABo4H+MIH7MBYGA1UdJQEB/wQMMAoGCCsGAQUFBwMJMB0GA1UdDgQW
BBR9/5CuRokEgGiqSzYuZGYAogl8TzCBoAYDVR0gBIGYMIGVMIGSBgorBgEEAc4f
AwEBMIGDMFgGCCsGAQUFBwICMEweSgBBAGkAbgB1AGwAdAAgAHQAZQBzAHQAaQBt
AGkAcwBlAGsAcwAuACAATwBuAGwAeQAgAGYAbwByACAAdABlAHMAdABpAG4AZwAu
MCcGCCsGAQUFBwIBFhtodHRwOi8vd3d3LnNrLmVlL2FqYXRlbXBlbC8wHwYDVR0j
BBgwFoAUtTQKnaUvEMXnIQ6+xLFlRxsDdv4wDQYJKoZIhvcNAQEFBQADggEBAAba
j7kTruTAPHqToye9ZtBdaJ3FZjiKug9/5RjsMwDpOeqFDqCorLd+DBI4tgdu0g4l
haI3aVnKdRBkGV18kqp84uU97JRFWQEf6H8hpJ9k/LzAACkP3tD+0ym+md532mV+
nRz1Jj+RPLAUk9xYMV7KPczZN1xnl2wZDJwBbQpcSVH1DjlZv3tFLHBLIYTS6qOK
4SxStcgRq7KdRczfW6mfXzTCRWM3G9nmDei5Q3+XTED41j8szRWglzYf6zOv4djk
ja64WYraQ5zb4x8Xh7qTCk6UupZ7je+0oRfuz0h/3zyRdjcRPkjloSpQp/NG8Rmr
cnr874p8d9fdwCrRI7U=
-----END CERTIFICATE-----
CERT;
    }
}
