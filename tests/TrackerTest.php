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

use KG\DigiDoc\Tracker;

class TrackeTest extends \PHPUnit_Framework_TestCase
{
    public function testAddSingleObject()
    {
        $tracker = new Tracker();
        $tracker->add($object = new \stdClass());

        $this->assertTrue($tracker->has($object));
    }

    public function testAddArrayAddsChildren()
    {
        $tracker = new Tracker();
        $tracker->add($array = array(
            $objectA = new \stdClass(),
            $objectB = new \stdClass(),
        ));

        $this->assertTrue($tracker->has($objectA));
        $this->assertTrue($tracker->has($objectB));
        $this->assertFalse($tracker->has($array));
    }
}
