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

use KG\DigiDoc\Native\Stamp;
use VirtualFileSystem\FileSystem;

class StampTest extends \PHPUnit_Framework_TestCase
{
    public function testAddFile()
    {
        $fs = new FileSystem();
        file_put_contents($fs->path('/foo.txt'), $text = 'Hello, world!');

        $stamp = new Stamp();
        $stamp->addFile('example.txt', $fs->path('/foo.txt'));

        $this->assertEquals(
            hash('sha256', $text, true),
            $stamp->getFileDigest('example.txt')
        );
    }
}
