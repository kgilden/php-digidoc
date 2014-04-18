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

class CertificateInfo
{
    /**
     *
     * @var string $Issuer
     * @access public
     */
    public $Issuer = null;

    /**
     *
     * @var string $Subject
     * @access public
     */
    public $Subject = null;

    /**
     *
     * @var dateTime $ValidFrom
     * @access public
     */
    public $ValidFrom = null;

    /**
     *
     * @var dateTime $ValidTo
     * @access public
     */
    public $ValidTo = null;

    /**
     *
     * @var string $IssuerSerial
     * @access public
     */
    public $IssuerSerial = null;

    /**
     *
     * @var CertificatePolicy $Policies
     * @access public
     */
    public $Policies = null;

    /**
     *
     * @param string $Issuer
     * @param string $Subject
     * @param dateTime $ValidFrom
     * @param dateTime $ValidTo
     * @param string $IssuerSerial
     * @param CertificatePolicy $Policies
     * @access public
     */
    public function __construct($Issuer, $Subject, $ValidFrom, $ValidTo, $IssuerSerial, $Policies)
    {
        $this->Issuer = $Issuer;
        $this->Subject = $Subject;
        $this->ValidFrom = $ValidFrom;
        $this->ValidTo = $ValidTo;
        $this->IssuerSerial = $IssuerSerial;
        $this->Policies = $Policies;
    }
}
