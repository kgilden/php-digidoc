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
     * @expectedException KG\DigiDoc\Exception\UnexpectedTypeException
     */
    public function testConstructFailsIfNotString()
    {
        $container = new FileContainer(false);
    }

    public function testToBase64EncodesEntireContainer()
    {
        file_put_contents(
            $fileName = tempnam(sys_get_temp_dir(), 'digidoctest'),
            $content = "Hello, world!"
        );

        try {
            $container = new FileContainer($fileName);

            $this->assertEquals(base64_encode($content), $container->toBase64());
            unlink($fileName);
        } catch (\Exception $e) {
            // Cleanup just in case.
            unlink($fileName);

            throw $e;
        }
    }

    /**
     * @expectedException KG\DigiDoc\Exception\RuntimeException
     */
    public function testToBase64FailsIfFileNotExists()
    {
        $container = new FileContainer('foo/baz');
        $container->toBase64();
    }
}
