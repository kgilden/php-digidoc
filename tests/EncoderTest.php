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

use KG\DigiDoc\Encoder;

class EncoderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string[]
     */
    protected $filePaths;

    public function testEncodeConvertsToBase64()
    {
        $encoder = new Encoder();
        $this->assertContains('YW55IGNhcm5hbCBwbGVhc3U=', $encoder->encode('any carnal pleasu'));
    }

    public function testEncodeFileContent()
    {
        $path = $this->createFileWithContent('any carnal pleasu');

        $encoder = new Encoder();
        $this->assertContains('YW55IGNhcm5hbCBwbGVhc3U=', $encoder->encodeFileContent($path));
    }

    public function testEncodeAddsNewline()
    {
        $encoder = new Encoder();
        $this->assertStringEndsWith("\n", $encoder->encode('any carnal pleasu'));
    }

    public function testEncodeSplitsIntoMultipleLines()
    {
        $text = str_repeat('a', 96);

        $encoder = new Encoder();

        $lines = explode("\n", trim($encoder->encode($text)));
        $this->assertCount(2, $lines);

        foreach ($lines as $line) {
            $this->assertEquals(64, strlen($line));
        }
    }

    public function testDecodeConcatsFromNewlines()
    {
        $text = "YWFh\nYWFh";

        $encoder = new Encoder();
        $this->assertEquals('aaaaaa', $encoder->decode($text));
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
    private function createFileWithContent($content = '')
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
