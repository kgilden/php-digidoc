<?php

/*
 * This file is part of the DigiDoc package.
 *
 * (c) Kristen Gilden <kristen.gilden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KG\DigiDoc\Tests\Native;

use KG\DigiDoc\Native\Certificate;

class CertificateTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \PHPUnit_Framework_Error
     * @expectedExceptionMessage openssl_x509_read(): supplied parameter cannot be coerced into an X509 certificate!
     */
    public function testConstructFailsIfInvalidCert()
    {
        $cert = new Certificate('not-cert-data');
    }

    public function testCertificateAcceptsProperCert()
    {
        $cert = new Certificate($this->getCertData());
    }

    private function getCertData()
    {
        return file_get_contents(__DIR__ . '/../fixtures/39104040377.cer');
    }
}
