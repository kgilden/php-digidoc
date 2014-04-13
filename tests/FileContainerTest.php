<?php

/*
 * This file is part of the DigiDoc package.
 *
 * (c) Kristen Gilden <kristen.gilden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KG\DigiDoc\Tests;

use KG\DigiDoc\FileContainer;

class FileContainerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string[]
     */
    protected $filePaths;

    /**
     * @expectedException KG\DigiDoc\Exception\UnexpectedTypeException
     */
    public function testConstructFailsIfNotString()
    {
        $container = new FileContainer($this->getMockApi(), false);
    }

    public function testContainerCompatibleWithFileFunctions()
    {
        $container = new FileContainer(
            $this->getMockApi(),
            $this->createFileWithContent('Hello, world!')
        );

        $this->assertTrue(file_exists($container));
    }

    public function testCreateSignatureDelegatesCallToApi()
    {
        $api = $this->getMockApi();

        $certificate = $this->getMockCertificate();

        $api
            ->expects($this->once())
            ->method('createSignature')
            ->with($certificate)
            ->will($this->returnValue($expectedSignature = 'foo'))
        ;

        $container = new FileContainer($api, $this->createFileWithContent('fubar'));
        $signature = $container->createSignature($certificate);

        $this->assertSame($expectedSignature, $signature);
    }

    public function testWriteSkippedIfNothingChanged()
    {
        $this->registerFilePath($filePath = sys_get_temp_dir().'/digidoc_test_write_skipped');

        $this->assertFileNotExists($filePath);

        $container = new FileContainer($this->getMockApi(), $filePath, false);
        $container->write();

        $this->assertFileNotExists($filePath);
    }

    public function testWriteStoresContentsFromApiOnDisk()
    {
        $filePath = $this->createTempFile('');

        $api = $this->getMockApi();

        $api
            ->expects($this->atLeastOnce())
            ->method('isSessionOpened')
            ->will($this->returnValue(true))
        ;

        $api
            ->expects($this->once())
            ->method('getContents')
            ->will($this->returnValue('foo'))
        ;

        $container = new FileContainer($api, $filePath);

        // Do something, which should theoretically open up a session.
        $container->createSignature($this->getMockCertificate());
        $container->write();

        $this->assertFileExists($filePath);
        $this->assertEquals('foo', file_get_contents($filePath));
    }

    /**
     * @expectedException \KG\DigiDoc\Exception\UnexpectedTypeException
     *
     * @return [type]
     */
    public function  testAddFileFailsIfNotStringNorFile()
    {
        $container = new FileContainer($this->getMockApi(), $this->createTempFile());
        $container->addFile(new \stdClass());
    }

    public function testAddFileDelegatesCallToApi()
    {
        $api = $this->getMockApi();
        $this->mockOpenSession($api, $this->getMockSession());

        $api
            ->expects($this->once())
            ->method('addFile')
        ;

        $newFile = $this->createTempFile();

        $container = new FileContainer($api, $this->createTempFile());
        $container->addFile($newFile);
    }

    /**
     * @todo Improve the test
     */
    public function testIsSerializable()
    {
        $container = new FileContainer($this->getMockApi(), $this->createTempFile());

        $serialized = serialize($container);
        $newContainer = unserialize($serialized);
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

    /**
     * Creates a temporary file.
     *
     * @return string Path to the file
     */
    private function createTempFile($filePath = null)
    {
        $filePath = tempnam(sys_get_temp_dir(), 'digidoc_test_');

        $this->registerFilePath($filePath);

        return $filePath;
    }

    /**
     * Creates a temporary file with the given content.
     *
     * @param string $content
     *
     * @return string Path to the file
     */
    private function createFileWithContent($content = '')
    {
        file_put_contents($filePath = $this->createTempFile(), $content);

        $this->filePaths[] = $filePath;

        return $filePath;
    }

    /**
     * @return \KG\DigiDoc\Api|PHPUnit_Framework_MockObject_MockObject
     */
    private function getMockApi()
    {
        return $this
            ->getMockBuilder('KG\DigiDoc\Api')
            ->disableOriginalConstructor()
            ->getMock()
        ;
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
     * @param \PHPUnit_Framework_MockObject_MockObject $mock
     * @param \KG\DigiDoc\Session                      $session
     */
    private function mockOpenSession(\PHPUnit_Framework_MockObject_MockObject $mock, $session)
    {
        $mock
            ->expects($this->once())
            ->method('openSession')
            ->will($this->returnValue($session))
        ;
    }
}
