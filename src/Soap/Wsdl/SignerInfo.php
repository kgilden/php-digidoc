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

class SignerInfo
{
    /**
     *
     * @var string $CommonName
     * @access public
     */
    public $CommonName = null;

    /**
     *
     * @var string $IDCode
     * @access public
     */
    public $IDCode = null;

    /**
     *
     * @var CertificateInfo $Certificate
     * @access public
     */
    public $Certificate = null;

    /**
     *
     * @param string $CommonName
     * @param string $IDCode
     * @param CertificateInfo $Certificate
     * @access public
     */
    public function __construct($CommonName, $IDCode, $Certificate)
    {
        $this->CommonName = $CommonName;
        $this->IDCode = $IDCode;
        $this->Certificate = $Certificate;
    }
}
