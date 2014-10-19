<?php

/*
 * This file is part of the DigiDoc package.
 *
 * (c) Kristen Gilden <kristen.gilden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KG\DigiDoc\Tests\Envelope;

use KG\DigiDoc\Native\Envelope;

class EnvelopeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \RuntimeException
     */
    public function testGetFilesFailsIfArchiveFailsToOpen()
    {
        $envelope = new Envelope('not-exists.bdoc');
    }

    public function testGetFilesReturnsIterator()
    {
        $envelope = new Envelope(__DIR__ . '/../fixtures/envelope.bdoc');
        $this->assertInstanceOf('\Iterator', $envelope->getFiles());
    }

    public function testGetFilesNotContainsMetaFiles()
    {
        $path = __DIR__ . '/../fixtures/envelope.bdoc';

        $envelope = new Envelope($path);
        $expected = array(
            'zip://' . $path . '#' . 'hello.txt',
            'zip://' . $path . '#' . 'kitten.jpg',
        );

        $this->assertEquals($expected, iterator_to_array($envelope->getFiles()));
    }

    public function testGetStampsReturnsIterator()
    {
        $envelope = new Envelope(__DIR__ . '/../fixtures/envelope.bdoc');
        $this->assertInstanceOf('\Iterator', $envelope->getStamps());
    }

    public function testGetStampsContainsOnlyStampObjects()
    {
        $path = __DIR__ . '/../fixtures/envelope.bdoc';

        $envelope = new Envelope($path);

        $stamps = iterator_to_array($envelope->getStamps());

        $this->assertCount(2, $stamps);
        foreach ($stamps as $stamp) {
            $this->assertInstanceOf('KG\DigiDoc\Native\Stamp', $stamp);
        }
    }
}
