<?php

namespace KG\Tests\DigiDoc\Ocsp;

/*
 * This file is part of the DigiDoc package.
 *
 * (c) Kristen Gilden <kristen.gilden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use KG\DigiDoc\Ocsp\Asn1;
use KG\DigiDoc\Ocsp\Response;
use phpseclib\File\ASN1 as Asn1Parser;

class ResponseTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The OCSP response status was not "successful (0)", got 2 instead
     */
    public function testConstructFailsIfResponseStatusNotSuccessful()
    {
        new Response($this->createDerResponse(array(
            'responseStatus' => Asn1::OCSP_INTERNAL_ERROR
        )));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Unknown response type "1.3.6"
     */
    public function testConstructFailsIfUnknownResponseType()
    {
        new Response($this->createDerResponse(array(
            'responseStatus' => Asn1::OCSP_SUCCESSFUL,
            'responseBytes' => array(
                'responseType' => '1.3.6', // some random OID
                'response' => base64_encode('Hello, world!'),
            ),
        )));
    }

    public function testGetContent()
    {
        $response = new Response($content = $this->getResponseBer());
        $this->assertEquals($content, $response->getContent());
    }

    public function testResponseImplementsToString()
    {
        $response = new Response($content = $this->getResponseBer());
        $this->assertEquals($content, (string) $response);
    }

    public function testGetStatusReturnsInteger()
    {
        $response = new Response($this->getResponseBer());

        $this->assertSame(Asn1::OCSP_SUCCESSFUL, $response->getStatus());
    }

    /**
     * @group foo
     */
    public function testNonceCorrect()
    {
        $response = new Response($this->getResponseBer());

        $this->assertTrue($response->isNonceEqualTo(pack("H*" , '0410c3204485aa9860df89c81b858fb09cd8')));
    }

    /**
     * Creates a DER response for testing
     *
     * @param mixed $source
     *
     * @return string
     */
    private function createDerResponse($source)
    {
        $parser = new Asn1Parser();
        $asn1 = new Asn1();

        return $parser->encodeDER($source, $asn1->OCSPResponse);
    }

    private function getResponseBer()
    {
        return base64_decode(
            'MIICVwoBAKCCAlAwggJMBgkrBgEFBQcwAQEEggI9MIICOTCCASGhgYYwgYMxCzAJBgNVBAYTAkVF' .
            'MSIwIAYDVQQKDBlBUyBTZXJ0aWZpdHNlZXJpbWlza2Vza3VzMQ0wCwYDVQQLDARPQ1NQMScwJQYD' .
            'VQQDDB5URVNUIG9mIFNLIE9DU1AgUkVTUE9OREVSIDIwMTExGDAWBgkqhkiG9w0BCQEWCXBraUBz' .
            'ay5lZRgPMjAxNDEyMjAxMzQ0MDhaMGAwXjBJMAkGBSsOAwIaBQAEFJ8hzI+QiAAqq1ikY3MvViFZ' .
            'KzWuBBR7avJVUFy42XoIh0Gu+qIrPVtXdgIQH/v/rqwJX11SX33gZ4PrfYAAGA8yMDE0MTIyMDEz' .
            'NDQwOFqhIzAhMB8GCSsGAQUFBzABAgQSBBDDIESFqphg34nIG4WPsJzYMA0GCSqGSIb3DQEBBQUA' .
            'A4IBAQAPE20l+8tr4pZv1Mm6Fsad7ZP7tVlNb+jl0KxWrD3InL+Q38tzrlo8XHn2AMkkscqclPgZ' .
            'KQSm3Gz4ZtI61sV7nPBI5UTlCpCcstlDaMoL2FjEn4mwIt2pJg1lYdMH0Lg5pZZzjZw5F51PWv9C' .
            'zSqhlPtXSYWretux8ZlWdwqX4VUvm4jBT18J0c9hjhWr85FJhFo2QcMAsX5lEuYLdjlbb5+3fCw1' .
            '/0FZ1h8xFp6Yiu4Bp3il5Um1YSnUXHxLtA4ScZ+vMI9n0aFmQJBdxrq/7Kd3GsPcOcWlgiSaqTgA' .
            'V9uaoOe/hQSF1O/MC8dpTzhClAfaPCiU6lVa2yUn8JIV'
        );
    }
}
