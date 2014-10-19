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

class Signature
{
    /**
     * @var Envelope
     */
    private $envelope;

    /**
     * @var DOMDocument
     */
    private $dom;

    /**
     * @param Envelope    $envelope Envelope containing the signature
     * @param DomDocument $dom      DOM of the signature XML
     */
    public function __construct(Envelope $envelope, \DOMDocument $dom)
    {
        $this->envelope = $envelope;
        $this->dom = $dom;
    }
}
