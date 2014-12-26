<?php

/*
 * This file is part of the DigiDoc package.
 *
 * (c) Kristen Gilden <kristen.gilden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KG\DigiDoc\Tests\X509;

use KG\DigiDoc\X509\Cert;

class CertTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \PHPUnit_Framework_Error
     * @expectedExceptionMessage openssl_x509_read(): supplied parameter cannot be coerced into an X509 certificate!
     */
    public function testConstructFailsIfInvalidCert()
    {
        $cert = new Cert('not-cert-data');
    }

    public function testCertAcceptsProperCert()
    {
        $cert = new Cert($this->getCertData());
    }

    public function testGetIssuerCN()
    {
        $cert = new Cert($this->getCertData());
        $this->assertSame('ESTEID-SK 2011', $cert->getIssuerCN());
    }

    public function testToStringReturnsDerFormat()
    {
        $cert = new Cert($this->getCertData());
        $this->assertEquals(bin2hex($this->getCertInDer()), bin2hex((string) $cert));
    }

    public function testHasSignedPassesPublicKeyToSignature()
    {
        $testCase = $this;

        $signature = $this->getMockSignature();
        $signature
            ->expects($this->once())
            ->method('isSignedByKey')
            ->will($this->returnCallback(function ($key) use ($testCase) {

                $testCase->assertEquals('OpenSSL key', get_resource_type($key));

                $expectedKey =
                    "-----BEGIN PUBLIC KEY-----\n" .
                    "MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA0QQstOYZrcQvzPCbh4HD\n" .
                    "SQjGJMOWs3OzZ1kDbM8cyrF+jqiCdWq5vWU/ThGYmz6h+ZF4nipFOhTPPROsoeEL\n" .
                    "4uaCSxhvCBP2C2UtQBcvKCwSj+6J9adgxoIqwE6EIMRTx2b+tytxapBTbkSWBObr\n" .
                    "tvcTuRlhXVqVI6+X8lFXmM7YEKHbsYjCJ21FlN850FrVh65uzUB3Q9Ghr1exkwi1\n" .
                    "JSvZYn6AygEpjfLCCLBbHcg7MdzQoGOAICfOJgb+kxPwrBOmbkIVXWi6y5ap0oEN\n" .
                    "fYgk5hNHiQZoJPQebKb7Hs8ehDhrC4wLLjqdjQtYQKZlmLOS8TOHn1sRh3a6s/qB\n" .
                    "lwIDAQAB\n" .
                    "-----END PUBLIC KEY-----\n"
                ;

                $details = openssl_pkey_get_details($key);
                $testCase->assertEquals($expectedKey, $details['key']);

                return true;
            }))
        ;

        $cert = new Cert($this->getCertData());
        $this->assertTrue($cert->hasSigned($signature));
    }

    private function getMockSignature()
    {
        return $this
            ->getMockBuilder('KG\DigiDoc\X509\Signature')
            ->disableOriginalConstructor()
            ->getMock()
        ;;
    }

    private function getCertData()
    {
        return sprintf(
            "-----BEGIN CERTIFICATE-----\n%s-----END CERTIFICATE-----\n",
            chunk_split(base64_encode($this->getCertInDer()), 64, "\n")
        );
    }

    private function getCertInDer()
    {
        $armoredCert = <<<CERT
MIIEoTCCA4mgAwIBAgIQH/v/rqwJX11SX33gZ4PrfTANBgkqhkiG9w0BAQUF
ADBkMQswCQYDVQQGEwJFRTEiMCAGA1UECgwZQVMgU2VydGlmaXRzZWVyaW1p
c2tlc2t1czEXMBUGA1UEAwwORVNURUlELVNLIDIwMTExGDAWBgkqhkiG9w0B
CQEWCXBraUBzay5lZTAeFw0xMzEwMTcwNjA0MTZaFw0xNjAzMjkyMTAwMDBa
MIGYMQswCQYDVQQGEwJFRTEPMA0GA1UECgwGRVNURUlEMRowGAYDVQQLDBFk
aWdpdGFsIHNpZ25hdHVyZTEjMCEGA1UEAwwaR0lMREVOLEtSSVNURU4sMzkx
MDQwNDAzNzcxDzANBgNVBAQMBkdJTERFTjEQMA4GA1UEKgwHS1JJU1RFTjEU
MBIGA1UEBRMLMzkxMDQwNDAzNzcwggEiMA0GCSqGSIb3DQEBAQUAA4IBDwAw
ggEKAoIBAQDRBCy05hmtxC/M8JuHgcNJCMYkw5azc7NnWQNszxzKsX6OqIJ1
arm9ZT9OEZibPqH5kXieKkU6FM89E6yh4Qvi5oJLGG8IE/YLZS1AFy8oLBKP
7on1p2DGgirAToQgxFPHZv63K3FqkFNuRJYE5uu29xO5GWFdWpUjr5fyUVeY
ztgQoduxiMInbUWU3znQWtWHrm7NQHdD0aGvV7GTCLUlK9lifoDKASmN8sII
sFsdyDsx3NCgY4AgJ84mBv6TE/CsE6ZuQhVdaLrLlqnSgQ19iCTmE0eJBmgk
9B5spvsezx6EOGsLjAsuOp2NC1hApmWYs5LxM4efWxGHdrqz+oGXAgMBAAGj
ggEYMIIBFDAJBgNVHRMEAjAAMA4GA1UdDwEB/wQEAwIGQDBRBgNVHSAESjBI
MEYGCysGAQQBzh8BAQMDMDcwEgYIKwYBBQUHAgIwBhoEbm9uZTAhBggrBgEF
BQcCARYVaHR0cDovL3d3dy5zay5lZS9jcHMvMB0GA1UdDgQWBBRKeIr0Gdys
dJm8+CkZDyWFG8sE6jAiBggrBgEFBQcBAwQWMBQwCAYGBACORgEBMAgGBgQA
jkYBBDAfBgNVHSMEGDAWgBR7avJVUFy42XoIh0Gu+qIrPVtXdjBABgNVHR8E
OTA3MDWgM6Axhi9odHRwOi8vd3d3LnNrLmVlL3JlcG9zaXRvcnkvY3Jscy9l
c3RlaWQyMDExLmNybDANBgkqhkiG9w0BAQUFAAOCAQEAfvjXpkWn5HRJpvxm
n+h3KO9HfmcdHvIZIikaRoKLKg03nPn+ARETDjzZq62fuDamgyyXrTahdpAc
te+VgEmr863aOd3El37P8w439VGK1vKcwhIiNpiyvn0n5NlxP2YunItUFRqt
5MMbPlyIzNTR3KrQaNWGHvOVB4dxVEF6Bi3b2EZFIz8x5XOWD+UKGgpkcV3b
p5OoxSlcQh4fTNYjBvWKOr0aeC8RfcuPxiucreaFiE5jTWRgDVJ4qxRVGReT
Lw3LwwNh+ZS4hcEOSa0rLHCNAAUGHbOs1sCXyX5zjKAAnKIue+a42CyffYid
b9eQG5FzeFlmSse1SmphHfsP3A==
CERT;

        return base64_decode(str_replace("\n", '', $armoredCert));
    }
}
