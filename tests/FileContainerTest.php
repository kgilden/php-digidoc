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
        $container = new FileContainer($this->getMockApi(), false);
    }

    public function testContainerCompatibleWithFileFunctions()
    {
        file_put_contents(
            $fileName = tempnam(sys_get_temp_dir(), 'digidoctest'),
            $content = "Hello, world!"
        );

        try {
            $container = new FileContainer($this->getMockApi(), $fileName);

            $this->assertTrue(file_exists($container));

            unlink($fileName);
        } catch (\Exception $e) {
            // Cleanup just in case.
            unlink($fileName);

            throw $e;
        }
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
}
