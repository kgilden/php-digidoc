<?php

/*
 * This file is part of the DigiDoc package.
 *
 * (c) Kristen Gilden <kristen.gilden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KG\DigiDoc;

/**
 * Represents a certificate.
 */
class Certificate
{
    /**
     * The certificate id seems to be a 128-bit hex encoded string based on
     * the DigiDoc documentation. This can be retrieved from the client
     * via the JavaScript library. It's not sent via a regular SSL request.
     *
     * @var string
     */
    private $id;

    /**
     * Certificate signature can also be retrieved from the client using the
     * JavaScript library. This one is also passed via a regular SSL request.
     *
     * @var string
     */
    private $signature;

    /**
     * @param string $id        Referred to as "token id" in DigiDoc manual
     * @param string $signature The (hex) signature
     */
    public function __construct($id, $signature)
    {
        $this->id = $id;
        $this->signature = $signature;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Gets the certificate signature. NB! This is not a signature of a
     * digitally signed document.
     *
     * @return string
     */
    public function getSignature()
    {
        return $this->signature;
    }
}
