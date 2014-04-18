<?php

/*
 * This file is part of the DigiDoc package.
 *
 * (c) Kristen Gilden <kristen.gilden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KG\DigiDoc\Soap\Wsdl;

class CRLInfo
{

    /**
     *
     * @var string $Issuer
     * @access public
     */
    public $Issuer = null;

    /**
     *
     * @var dateTime $LastUpdate
     * @access public
     */
    public $LastUpdate = null;

    /**
     *
     * @var dateTime $NextUpdate
     * @access public
     */
    public $NextUpdate = null;

    /**
     *
     * @var RevokedInfo $Revocations
     * @access public
     */
    public $Revocations = null;

    /**
     *
     * @param string $Issuer
     * @param dateTime $LastUpdate
     * @param dateTime $NextUpdate
     * @param RevokedInfo $Revocations
     * @access public
     */
    public function __construct($Issuer, $LastUpdate, $NextUpdate, $Revocations)
    {
        $this->Issuer = $Issuer;
        $this->LastUpdate = $LastUpdate;
        $this->NextUpdate = $NextUpdate;
        $this->Revocations = $Revocations;
    }
}
