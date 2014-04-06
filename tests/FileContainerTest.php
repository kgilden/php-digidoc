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
        $this->mockOpenSession($api, $session = $this->getMockSession());

        $certificate = $this->getMockCertificate();

        $api
            ->expects($this->once())
            ->method('createSignature')
            ->with($session, $certificate)
            ->will($this->returnValue($expectedSignature = 'foo'))
        ;

        $container = new FileContainer($api, $this->createFileWithContent('fubar'));
        $signature = $container->createSignature($certificate);

        $this->assertSame($expectedSignature, $signature);
    }

    protected function setUp()
    {
        $this->filePaths = array();
    }

    protected function tearDown()
    {
        foreach ($this->filePaths as $filePath) {
            unlink($filePath);
        }
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
        file_put_contents(
            $filePath = tempnam(sys_get_temp_dir(), 'digidoc_test_'),
            $content
        );

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
