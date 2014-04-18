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

class ConfirmationInfo
{
    /**
     *
     * @var string $ResponderID
     * @access public
     */
    public $ResponderID = null;

    /**
     *
     * @var string $ProducedAt
     * @access public
     */
    public $ProducedAt = null;

    /**
     *
     * @var CertificateInfo $ResponderCertificate
     * @access public
     */
    public $ResponderCertificate = null;

    /**
     *
     * @param string $ResponderID
     * @param string $ProducedAt
     * @param CertificateInfo $ResponderCertificate
     * @access public
     */
    public function __construct($ResponderID, $ProducedAt, $ResponderCertificate)
    {
        $this->ResponderID = $ResponderID;
        $this->ProducedAt = $ProducedAt;
        $this->ResponderCertificate = $ResponderCertificate;
    }
}
