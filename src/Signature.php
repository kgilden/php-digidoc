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
     * @var Certificate|null
     */
    private $certificate;

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
     * @param Certificate $certificate
     */
    public function __construct(Certificate $certificate)
    {
        $this->certificate = $certificate;
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
        $refl = new \ReflectionClass(get_called_class());

        $signature = $refl->newInstanceWithoutConstructor();
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
     * Gets the person's certificate. This most likely returns null, when an
     * existing DigiDoc container is opened.
     *
     * @api
     *
     * @return Certificate|null
     */
    public function getCertificate()
    {
        return $this->certificate;
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
