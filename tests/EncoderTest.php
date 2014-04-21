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
    public function testEncodeConvertsToBase64()
    {
        $encoder = new Encoder();
        $this->assertContains('YW55IGNhcm5hbCBwbGVhc3U=', $encoder->encode('any carnal pleasu'));
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
}
