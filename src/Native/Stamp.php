<?php

/*
 * This file is part of the DigiDoc package.
 *
 * (c) Kristen Gilden <kristen.gilden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KG\DigiDoc\Native;

use DOMDocument;

class Stamp
{
    /**
     * @var DOMDocument
     */
    private $dom;

    /**
     * @param DomDocument $dom DOM of the stamp's XML representation
     */
    public function __construct(\DOMDocument $dom)
    {
        $this->dom = $dom;
    }
}
