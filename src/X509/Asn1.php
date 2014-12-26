<?php

/*
 * This file is part of the DigiDoc package.
 *
 * (c) Kristen Gilden <kristen.gilden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KG\DigiDoc\X509;

use phpseclib\File\X509 as BaseAsn1;

/**
 * A subset of X509 ASN.1 used by this library. Casing of properties matches
 * the spec.
 *
 * @see https://tools.ietf.org/html/rfc5280#appendix-A
 */
class Asn1
{
    /**
     * @see https://tools.ietf.org/html/rfc5280#section-4.1.1.2
     *
     * @var array
     */
    public $AlgorithmIdentifier;

    /**
     * @see https://tools.ietf.org/html/rfc5280#section-4.1
     *
     * @var array
     */
    public $Certificate;

    /**
     * @see https://tools.ietf.org/html/rfc5280#section-4.1
     *
     * @var array
     */
    public $CertificateSerialNumber;

    /**
     * @see https://tools.ietf.org/html/rfc5280#section-5.3.1
     *
     * @var array
     */
    public $CRLReason;

    /**
     * @see https://tools.ietf.org/html/rfc5280#section-4.1
     *
     * @var array
     */
    public $Extensions;

    /**
     * @see https://tools.ietf.org/html/rfc5280#section-4.1.2.4
     *
     * @var array
     */
    public $Name;

    /**
     * @var BaseAsn1
     */
    private $baseAsn1;

    public function __construct()
    {
        $this->baseAsn1 = $baseAsn1 = new BaseAsn1();

        $TBSCertificate = $baseAsn1->Certificate['children']['tbsCertificate'];
        $this->AlgorithmIdentifier = $TBSCertificate['children']['signature'];
        $this->Certificate = $baseAsn1->Certificate;
        $this->CertificateSerialNumber = $TBSCertificate['children']['serialNumber'];
        $this->CRLReason = $baseAsn1->CRLReason;
        $this->Extensions = $baseAsn1->Extensions;
        $this->Name = $baseAsn1->Name;
    }

    /**
     * @param string $oid
     *
     * @return string
     *
     * @throws \RuntimeException If the oid is unknown
     */
    public function getValueForOid($oid)
    {
        if (isset($this->baseAsn1->oids[$oid])) {
            return $this->baseAsn1->oids[$oid];
        }

        throw new \RuntimeException(sprintf('Unknown oid "%s"', $oid));
    }
}
