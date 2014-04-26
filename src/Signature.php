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

use KG\DigiDoc\Soap\Wsdl\SignatureInfo;

class Signature
{
    /**
     * @var string
     */
    private $id;

    /**
     * The certificate id seems to be a 128-bit hex encoded string based on
     * the DigiDoc documentation. This can be retrieved from the client
     * via the JavaScript library. It's not sent via a regular SSL request.
     *
     * @var string
     */
    private $certId;

    /**
     * Certificate signature can also be retrieved from the client using the
     * JavaScript library. This one is also passed via a regular SSL request.
     *
     * @var string
     */
    private $certSignature;

    /**
     * @var string
     */
    private $challenge;

    /**
     * @var string
     */
    private $solution;

    /**
     * @var boolean
     */
    private $sealed = false;

    /**
     * @param string $certId        128-bit hex encoded certificate id
     * @param string $certSignature The actual hex encoded certificate signature
     */
    public function __construct($certId = null, $certSignature = null)
    {
        $this->certId = $certId;
        $this->certSignature = $certSignature;
    }

    /**
     * Creates a new Signature object from its Soap counterpart.
     *
     * @internal
     *
     * @param SignatureInfo $info
     *
     * @return Signature
     */
    public static function createFromSoap(SignatureInfo $info)
    {
        $signature = new static();
        $signature->id = $info->Id;
        $signature->seal();

        return $signature;
    }

    /**
     * @internal
     *
     * @param string $id
     *
     * @return Signature
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Sets the id of a signature. The id is basically a unique index within
     * a signed document to keep track of several signatures. They begin with
     * "S" followed by a number (e.g. 'S0', 'S5' etc.).
     *
     * @api
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Gets the person's certificate id. Not sure what it actually is.
     *
     * @todo clarify
     *
     * @api
     *
     * @return string|null A hex encoded certificate id
     */
    public function getCertId()
    {
        return $this->certId;
    }

    /**
     * Gets the certificate signature. NB! This is not a signature of a
     * digitally signed document. It's the actual signature verifying the client.
     *
     * @return string|null A hex encoded signature
     */
    public function getCertSignature()
    {
        return $this->certSignature;
    }

    /**
     * Sets the challenge to be solved by the client.
     *
     * @internal
     *
     * @param string $challenge
     *
     * @return Signature
     */
    public function setChallenge($challenge)
    {
        $this->challenge = $challenge;

        return $this;
    }

    /**
     * Gets the challenge to be solved by the client. This together with the
     * certificate id must be sent back and solved using the in-browser plugin.
     *
     * @api
     *
     * @return string|null
     */
    public function getChallenge()
    {
        return $this->challenge;
    }

    /**
     * @api
     *
     * @return boolean Whether the signature is finalized
     */
    public function isSealed()
    {
        return $this->sealed;
    }

    /**
     * Marks the current signature as sealed. Shouldn't be manually called.
     *
     * @internal
     *
     * @return Signature
     */
    public function seal()
    {
        $this->sealed = true;

        return $this;
    }

    /**
     * @api
     *
     * @param string $solution A solution to the current challenge
     *
     * @return Signature
     */
    public function setSolution($solution)
    {
        $this->solution = $solution;

        return $this;
    }

    /**
     * @internal
     *
     * @return string|null
     */
    public function getSolution()
    {
        return $this->solution;
    }
}
