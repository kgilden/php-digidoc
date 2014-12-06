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

use KG\DigiDoc\Ocsp\Request;
use org\bovigo\vfs\vfsStream;

class RequestTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException KG\DigiDoc\Exception\FileNotFoundException
     */
    public function testConstructFailsIfClientCertNotFound()
    {
        vfsStream::setUp('root', null, array(
            $nameOfIssuerCert = 'issuerCert.pem' => '',
        ));

        $pathToClientCert = vfsStream::url('root/clientCert.pem');
        $pathToIssuerCert = vfsStream::url('root/' . $nameOfIssuerCert);

        $this->assertFileNotExists($pathToClientCert);
        $this->assertFileExists($pathToIssuerCert);

        $request = new Request($pathToClientCert, $pathToIssuerCert);
    }

    /**
     * @expectedException KG\DigiDoc\Exception\FileNotFoundException
     */
    public function testConstructFailsIfIssuerCertNotFound()
    {
        vfsStream::setUp('root', null, array(
            $nameOfClientCert = 'clientCert.pem' => '',
        ));

        $pathToClientCert = vfsStream::url('root/' . $nameOfClientCert);
        $pathToIssuerCert = vfsStream::url('root/issuerCert.pem');

        $this->assertFileExists($pathToClientCert);
        $this->assertFileNotExists($pathToIssuerCert);

        $request = new Request($pathToClientCert, $pathToIssuerCert);
    }

    public function testGetPathToClientCert()
    {
        vfsStream::setUp('root', null, array(
            $nameOfClientCert = 'clientCert.pem' => '',
            $nameOfIssuerCert = 'issuerCert.pem' => '',
        ));

        $pathToClientCert = vfsStream::url('root/' . $nameOfClientCert);
        $pathToIssuerCert = vfsStream::url('root/' . $nameOfIssuerCert);

        $request = new Request($pathToClientCert, $pathToIssuerCert);
        $this->assertEquals($pathToClientCert, $request->getPathToClientCert());
    }

    public function testGetPathToIssuerCert()
    {
        vfsStream::setUp('root', null, array(
            $nameOfClientCert = 'clientCert.pem' => '',
            $nameOfIssuerCert = 'issuerCert.pem' => '',
        ));

        $pathToClientCert = vfsStream::url('root/' . $nameOfClientCert);
        $pathToIssuerCert = vfsStream::url('root/' . $nameOfIssuerCert);

        $request = new Request($pathToClientCert, $pathToIssuerCert);
        $this->assertEquals($pathToIssuerCert, $request->getPathToIssuerCert());
    }
}
