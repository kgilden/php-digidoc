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

class Signature
{
    /**
     * @var Api
     */
    private $api;

    /**
     * @var string
     */
    private $id;

    /**
     * @var Certificate
     */
    private $certificate;

    /**
     * @var string
     */
    private $challenge;

    /**
     * @var boolean
     */
    private $sealed = false;

    /**
     * @param Api         $api
     * @param Certificate $certificate
     * @param string      $id        Signature id
     * @param string      $challenge A solvable challenge to seal this signature
     */
    public function __construct(Api $api, Certificate $certificate, $id, $challenge)
    {
        $this->api = $api;
        $this->certificate = $certificate;
        $this->id = $id;
        $this->challenge = $challenge;
    }

    /**
     * Gets the signature id.
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    public function getCertificate()
    {
        return $this->certificate;
    }

    /**
     * @return string
     */
    public function getChallenge()
    {
        return $this->challenge;
    }

    /**
     * Seals the signature, effectively making the signature valid.
     *
     * @param string $solution
     */
    public function seal($solution)
    {
        return $this->sealed = $this->api->finishSignature($this, $solution);
    }

    /**
     * @return boolean Whether the current signature is sealed
     */
    public function isSealed()
    {
        return $this->sealed;
    }
}
