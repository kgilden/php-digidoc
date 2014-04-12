<?php

/*
 * This file is part of the DigiDoc package.
 *
 * (c) Kristen Gilden <kristen.gilden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KG\DigiDoc\Tests\ApiTest;

use KG\DigiDoc\Api;
use Symfony\Component\HttpFoundation\File\File;

class ApiTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string[]
     */
    protected $filePaths;

    public function testOpenSessionCreatesSession()
    {
        $client = $this->getMockClient();

        $client
            ->expects($this->once())
            ->method('__soapCall')
            ->with('StartSession')
            ->will($this->returnValue(array('Status' => 'OK', 'SessionId' => 1234)))
        ;

        $api = new Api($client);

        $this->assertInstanceOf('KG\DigiDoc\Session', $api->openSession());
    }

    public function testOpenSessionAddsFileContentsIfFileAdded()
    {
        $filePath = $this->createFileWithContents($contents = 'foobar');

        $client = $this->getMockClient();
        $client
            ->expects($this->once())
            ->method('__soapCall')
            ->with('StartSession', array('', base64_encode($contents)."\n", true, ''))
            ->will($this->returnValue(array('Status' => 'OK', 'SessionId' => 1234)))
        ;

        $api = new Api($client);
        $api->openSession(new File($filePath));
    }

    /**
     * @expectedException \KG\DigiDoc\Exception\ApiException
     */
    public function testOpenSessionFailsIfStatusIncorrect()
    {
        $client = $this->getMockClient();
        $client
            ->expects($this->once())
            ->method('__soapCall')
            ->will($this->returnValue(array('Status' => 'FOO', 'SessionId' => 1234)))
        ;

        $api = new Api($client);
        $api->openSession();
    }

    /**
     * @expectedException \KG\DigiDoc\Exception\ApiException
     */
    public function testCreateContainerFailsIfStatusIncorrect()
    {
        $client = $this->getMockClient();
        $client
            ->expects($this->once())
            ->method('__soapCall')
            ->with('CreateSignedDoc', array(null, Api::DOC_FORMAT, Api::DOC_VERSION))
            ->will($this->returnValue(array('Status' => 'FOO', 'SignedDocInfo' => null)))
        ;

        $api = new Api($client);
        $api->createContainer($this->getMockSession());
    }

    /**
     * @expectedException \KG\DigiDoc\Exception\ApiException
     */
    public function testAddFileFailsIfStatusIncorrect()
    {
        $filePath = $this->createFileWithContents($contents = 'foobar');
        $pathInfo = pathinfo($filePath);

        $client = $this->getMockClient();
        $client
            ->expects($this->once())
            ->method('__soapCall')
            ->with('AddDataFile', array(
                null,
                $pathInfo['filename'],
                'text/plain',
                Api::CONTENT_TYPE_EMBEDDED,
                strlen($contents),
                '',
                '',
                base64_encode($contents)."\n"
            ))
            ->will($this->returnValue(array('Status' => 'FOO', 'SignedDocInfo' => null)))
        ;

        $api = new Api($client);
        $api->addFile($this->getMockSession(), new File($filePath));
    }

    public function testCreateSignatureReturnsSignature()
    {
        $client = $this->getMockClient();
        $client
            ->expects($this->once())
            ->method('__soapCall')
            ->with('PrepareSignature')
            ->will($this->returnValue(array('Status' => 'OK', 'SignatureId' => 'DEAD', 'SignedInfoDigest' => 'BEEF')))
        ;

        $api = new Api($client);
        $signature = $api->createSignature($this->getMockSession(), $this->getMockCertificate());

        $this->assertEquals('BEEF', $signature->getChallenge());
    }

    /**
     * @expectedException \KG\DigiDoc\Exception\ApiException
     */
    public function testCreateSignatureFailsIfStatusIncorrect()
    {
        $client = $this->getMockClient();
        $client
            ->expects($this->once())
            ->method('__soapCall')
            ->with('PrepareSignature')
            ->will($this->returnValue(array('Status' => 'FOO', 'SignatureId' => '', 'SignedInfoDigest' => '')))
        ;

        $api = new Api($client);
        $api->createSignature($this->getMockSession(), $this->getMockCertificate());
    }

    public function testFinishSignatureReturnsTrueIfSuccessful()
    {
        $signatureId = 'S01';

        $info = new \stdClass();
        $info->SignatureInfo = new \stdClass();
        $info->SignatureInfo->Id = $signatureId;
        $info->SignatureInfo->Status = 'OK';

        $client = $this->getMockClient();
        $client
            ->expects($this->once())
            ->method('__soapCall')
            ->with('FinalizeSignature')
            ->will($this->returnValue(array('Status' => 'OK', 'SignedDocInfo' => $info)))
        ;

        $signature = $this->getMockSignature();
        $signature
            ->expects($this->atLeastOnce())
            ->method('getId')
            ->will($this->returnValue($signatureId))
        ;

        $api = new Api($client);

        $this->assertTrue($api->finishSignature($this->getMockSession(), $signature, 'DEADBEEF'));
    }

    /**
     * @expectedException \KG\DigiDoc\Exception\ApiException
     */
    public function testFinishSignatureFailsIfStatusIncorrect()
    {
        $info = new \stdClass();
        $info->SignatureInfo = new \stdClass();

        $client = $this->getMockClient();
        $client
            ->expects($this->once())
            ->method('__soapCall')
            ->with('FinalizeSignature')
            ->will($this->returnValue(array('Status' => 'FOO', 'SignedDocInfo' => $info)))
        ;

        $api = new Api($client);
        $api->finishSignature($this->getMockSession(), $this->getMockSignature(), 'DEADBEEF');
    }

    public function testSignatureFailsIfSignatureInvalid()
    {
        $signatureId = 'S01';

        $info = new \stdClass();
        $info->SignatureInfo = new \stdClass();
        $info->SignatureInfo->Id = $signatureId;
        $info->SignatureInfo->Status = 'ERROR';

        $client = $this->getMockClient();

        $client
            ->expects($this->at(0))
            ->method('__soapCall')
            ->with('FinalizeSignature')
            ->will($this->returnValue(array('Status' => 'OK', 'SignedDocInfo' => $info)))
        ;

        $client
            ->expects($this->at(1))
            ->method('__soapCall')
            ->with('RemoveSignature')
            ->will($this->returnValue(array('Status' => 'OK', 'SignedDocInfo' => null)))
        ;

        $signature = $this->getMockSignature();
        $signature
            ->expects($this->atLeastOnce())
            ->method('getId')
            ->will($this->returnValue($signatureId))
        ;

        $api = new Api($client);
        $this->assertFalse($api->finishSignature($this->getMockSession(), $signature, 'DEADBEEF'));
    }

    /**
     * @expectedException \KG\DigiDoc\Exception\ApiException
     */
    public function testRemoveSignatureFailsIfStatusIncorrect()
    {
        $client = $this->getMockClient();
        $client
            ->expects($this->once())
            ->method('__soapCall')
            ->with('RemoveSignature')
            ->will($this->returnValue(array('Status' => 'ERROR', 'SignedDocInfo' => null)))
        ;

        $api = new Api($client);
        $api->removeSignature($this->getMockSession(), $this->getMockSignature());
    }

    public function testGetContentsReturnsBase64DecodedData()
    {
        $expected = 'Hello, world!';

        $client = $this->getMockClient();
        $client
            ->expects($this->once())
            ->method('__soapCall')
            ->with('GetSignedDoc')
            ->will($this->returnValue(array('Status' => 'OK', 'SignedDocData' => chunk_split(base64_encode($expected), 4, "\n"))))
        ;

        $api = new Api($client);
        $this->assertEquals($expected, $api->getContents($this->getMockSession()));
    }

    /**
     * @expectedException \KG\DigiDoc\Exception\ApiException
     */
    public function testGetContentsFailsIfStatusIncorrect()
    {
        $client = $this->getMockClient();
        $client
            ->expects($this->once())
            ->method('__soapCall')
            ->with('GetSignedDoc')
            ->will($this->returnValue(array('Status' => 'ERROR', 'SignedDocData' => null)))
        ;

        $api = new Api($client);
        $api->getContents($this->getMockSession());
    }

    /**
     * @expectedException \KG\DigiDoc\Exception\ApiException
     */
    public function testCloseSessionFailsIfStatusIncorrect()
    {
        $client = $this->getMockClient();
        $client
            ->expects($this->once())
            ->method('__soapCall')
            ->with('CloseSession')
            ->will($this->returnValue(array('Status' => 'ERROR')))
        ;

        $api = new Api($client);
        $api->closeSession($this->getMockSession());
    }

    protected function setUp()
    {
        $this->filePaths = array();
    }

    protected function tearDown()
    {
        foreach ($this->filePaths as $filePath) {
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }
    }

    /**
     * Creates a temporary file with the given content.
     *
     * @todo Duplicate of FileContainerTest::createFileWithContent
     *
     * @param string $content
     *
     * @return string Path to the file
     */
    private function createFileWithContents($content = '')
    {
        file_put_contents($filePath = $this->createTempFile(), $content);

        return $filePath;
    }

    /**
     * Creates a temporary file.
     */
    private function createTempFile()
    {
        $filePath = tempnam(sys_get_temp_dir(), 'digidoc_test_');

        $this->registerFilePath($filePath);

        return $filePath;
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

    /**
     * @return \SoapClient|PHPUnit_Framework_MockObject_MockObject
     */
    private function getMockClient()
    {
        return $this
            ->getMockBuilder('SoapClient')
            ->disableOriginalConstructor()
            ->getMock()
        ;
    }

    /*
     * @return \Symfony\Component\HttpFoundation\File\File|PHPUnit_Framework_MockObject_MockObject
     */
    private function getMockFile()
    {
        return $this
            ->getMockBuilder('Symfony\Component\HttpFoundation\File\File')
            ->disableOriginalConstructor()
            ->getMock()
        ;
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

    /**
     * Registers a file path to ease cleanup. All registered file paths will
     * be removed from the disk after a test is run.
     *
     * @param string $filePath
     *
     * @return FileContainerTest
     */
    private function registerFilePath($filePath)
    {
        $this->filePaths[] = $filePath;

        return $this;
    }
}
