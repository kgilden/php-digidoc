<?php

/*
 * This file is part of the DigiDoc package.
 *
 * (c) Kristen Gilden <kristen.gilden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KG\DiciDoc\Soap\Wsdl;

class SignatureInfo
{
    /**
     *
     * @var string $Id
     * @access public
     */
    public $Id = null;

    /**
     *
     * @var string $Status
     * @access public
     */
    public $Status = null;

    /**
     *
     * @var Error $Error
     * @access public
     */
    public $Error = null;

    /**
     *
     * @var dateTime $SigningTime
     * @access public
     */
    public $SigningTime = null;

    /**
     *
     * @var SignerRole $SignerRole
     * @access public
     */
    public $SignerRole = null;

    /**
     *
     * @var SignatureProductionPlace $SignatureProductionPlace
     * @access public
     */
    public $SignatureProductionPlace = null;

    /**
     *
     * @var SignerInfo $Signer
     * @access public
     */
    public $Signer = null;

    /**
     *
     * @var ConfirmationInfo $Confirmation
     * @access public
     */
    public $Confirmation = null;

    /**
     *
     * @var TstInfo $Timestamps
     * @access public
     */
    public $Timestamps = null;

    /**
     *
     * @var CRLInfo $CRLInfo
     * @access public
     */
    public $CRLInfo = null;

    /**
     *
     * @param string $Id
     * @param string $Status
     * @param Error $Error
     * @param dateTime $SigningTime
     * @param SignerRole $SignerRole
     * @param SignatureProductionPlace $SignatureProductionPlace
     * @param SignerInfo $Signer
     * @param ConfirmationInfo $Confirmation
     * @param TstInfo $Timestamps
     * @param CRLInfo $CRLInfo
     * @access public
     */
    public function __construct($Id, $Status, $Error, $SigningTime, $SignerRole, $SignatureProductionPlace, $Signer, $Confirmation, $Timestamps, $CRLInfo)
    {
        $this->Id = $Id;
        $this->Status = $Status;
        $this->Error = $Error;
        $this->SigningTime = $SigningTime;
        $this->SignerRole = $SignerRole;
        $this->SignatureProductionPlace = $SignatureProductionPlace;
        $this->Signer = $Signer;
        $this->Confirmation = $Confirmation;
        $this->Timestamps = $Timestamps;
        $this->CRLInfo = $CRLInfo;
    }
}
