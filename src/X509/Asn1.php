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
     * AlgorithmIdentifier  ::=  SEQUENCE  {
     *      algorithm               OBJECT IDENTIFIER,
     *      parameters              ANY DEFINED BY algorithm OPTIONAL  }
     *                                 -- contains a value of the type
     *                                 -- registered for use with the
     *                                 -- algorithm object identifier value
     *
     * @var array
     */
    public $AlgorithmIdentifier;

    /**
     * Certificate  ::=  SEQUENCE  {
     *      tbsCertificate       TBSCertificate,
     *      signatureAlgorithm   AlgorithmIdentifier,
     *      signatureValue       BIT STRING  }
     *
     * @var array
     */
    public $Certificate;

    /**
     * CertificateSerialNumber  ::=  INTEGER
     *
     * @var array
     */
    public $CertificateSerialNumber;

    /**
     * CRLReason ::= ENUMERATED {
     *      unspecified             (0),
     *      keyCompromise           (1),
     *      cACompromise            (2),
     *      affiliationChanged      (3),
     *      superseded              (4),
     *      cessationOfOperation    (5),
     *      certificateHold         (6),
     *           -- value 7 is not used
     *      removeFromCRL           (8),
     *      privilegeWithdrawn      (9),
     *      aACompromise           (10) }
     *
     * @var array
     */
    public $CRLReason;

    /**
     * Extensions  ::=  SEQUENCE SIZE (1..MAX) OF Extension
     *
     * @var array
     */
    public $Extensions;

    /**
     * Name ::= CHOICE { -- only one possibility for now --
     *       rdnSequence  RDNSequence
     *
     * @var array
     */
    public $Name;

    public function __construct()
    {
        $baseAsn1 = new BaseAsn1();

        $TBSCertificate = $baseAsn1->Certificate['children']['tbsCertificate'];
        $this->AlgorithmIdentifier = $TBSCertificate['children']['signature'];
        $this->Certificate = $baseAsn1->Certificate;
        $this->CertificateSerialNumber = $TBSCertificate['children']['serialNumber'];
        $this->CRLReason = $baseAsn1->CRLReason;
        $this->Extensions = $baseAsn1->Extensions;
        $this->Name = $baseAsn1->Name;
    }
}
