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

use KG\DigiDoc\X509\Signature;

class SignatureTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testConstructFailsIfUnsupportedAlgorithmUsed()
    {
        $signature = new Signature('foo', 'bar', 'unsupported');
    }

    public function testSignedByKey()
    {
        $privateKey = openssl_pkey_new();
        $details = openssl_pkey_get_details($privateKey);
        $publicKey = openssl_pkey_get_public($details['key']);

        openssl_sign($bytesSigned = 'Hello, world!', $bytesOfSignature, $privateKey, 'sha1WithRSAEncryption');

        $signature = new Signature($bytesSigned, $bytesOfSignature, 'sha1WithRSAEncryption');

        $this->assertTrue($signature->isSignedByKey($publicKey));
    }

    public function testNotSignedByKey()
    {
        $privateKey = openssl_pkey_new();
        $details = openssl_pkey_get_details($privateKey);
        $publicKey = openssl_pkey_get_public($details['key']);

        $signature = new Signature('Hello, world!', 'foobar', 'sha1WithRSAEncryption');

        $this->assertFalse($signature->isSignedByKey($publicKey));
    }
}
