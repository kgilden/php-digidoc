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

class TstInfo
{
    /**
     *
     * @var string $Id
     * @access public
     */
    public $Id = null;

    /**
     *
     * @var string $Type
     * @access public
     */
    public $Type = null;

    /**
     *
     * @var string $SerialNumber
     * @access public
     */
    public $SerialNumber = null;

    /**
     *
     * @var dateTime $CreationTime
     * @access public
     */
    public $CreationTime = null;

    /**
     *
     * @var string $Policy
     * @access public
     */
    public $Policy = null;

    /**
     *
     * @var string $ErrorBound
     * @access public
     */
    public $ErrorBound = null;

    /**
     *
     * @var boolean $Ordered
     * @access public
     */
    public $Ordered = null;

    /**
     *
     * @var string $TSA
     * @access public
     */
    public $TSA = null;

    /**
     *
     * @var CertificateInfo $Certificate
     * @access public
     */
    public $Certificate = null;

    /**
     *
     * @param string $Id
     * @param string $Type
     * @param string $SerialNumber
     * @param dateTime $CreationTime
     * @param string $Policy
     * @param string $ErrorBound
     * @param boolean $Ordered
     * @param string $TSA
     * @param CertificateInfo $Certificate
     * @access public
     */
    public function __construct($Id, $Type, $SerialNumber, $CreationTime, $Policy, $ErrorBound, $Ordered, $TSA, $Certificate)
    {
        $this->Id = $Id;
        $this->Type = $Type;
        $this->SerialNumber = $SerialNumber;
        $this->CreationTime = $CreationTime;
        $this->Policy = $Policy;
        $this->ErrorBound = $ErrorBound;
        $this->Ordered = $Ordered;
        $this->TSA = $TSA;
        $this->Certificate = $Certificate;
    }
}
