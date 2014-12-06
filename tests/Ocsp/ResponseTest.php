<?php

namespace KG\Tests\DigiDoc\Ocsp;

/*
 * This file is part of the DigiDoc package.
 *
 * (c) Kristen Gilden <kristen.gilden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use KG\DigiDoc\Ocsp\Response;

class ResponseTest extends \PHPUnit_Framework_TestCase
{
    public function testGetContent()
    {
        $response = new Response('foo');
        $this->assertEquals('foo', $response->getContent());
    }

    public function testResponseImplementsToString()
    {
        $response = new Response('foo');
        $this->assertEquals('foo', (string) $response);
    }
}