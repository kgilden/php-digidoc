<?php

/*
 * This file is part of the DigiDoc package.
 *
 * (c) Kristen Gilden <kristen.gilden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KG\DigiDoc\Ocsp;

use KG\DigiDoc\X509\Asn1 as X509Asn1;
use phpseclib\File\ASN1 as BaseAsn1;

/**
 * A subset of OCSP ASN.1 used by this library. Casing of properties matches
 * the spec
 *
 * @see https://tools.ietf.org/html/rfc6960#appendix-B.1
 */
class Asn1
{
    const OCSP_SUCCESSFUL = 0;
    const OCSP_MALFORMED_REQUEST = 1;
    const OCSP_INTERNAL_ERROR = 2;
    const OCSP_TRY_LATER = 3;
    const OCSP_SIG_REQUIRED = 5;
    const OCSP_UNAUTHORIZED = 6;

    const OID_OCSP_NONCE = '1.3.6.1.5.5.7.48.1.2';

    /**
     * @see https://tools.ietf.org/html/rfc6960#section-4.2.1
     *
     * @var array
     */
    public $BasicOCSPResponse;

    /**
     * @see https://tools.ietf.org/html/rfc6960#section-4.1.1
     *
     * @var array
     */
    public $CertID;

    /**
     * @see https://tools.ietf.org/html/rfc6960#section-4.2.1
     *
     * @var array
     */
    public $CertStatus;

    /**
     * @see https://tools.ietf.org/html/rfc6960#section-4.2.1
     *
     * @var array
     */
    public $OCSPResponse;

    /**
     * @see https://tools.ietf.org/html/rfc6960#section-4.2.1
     *
     * @var array
     */
    public $OCSPResponseStatus;

    /**
     * @see https://tools.ietf.org/html/rfc6960#section-4.2.1
     * @see https://tools.ietf.org/html/rfc6960#section-4.2.2.3
     *
     * @var array
     */
    public $ResponderID;

    /**
     * @see https://tools.ietf.org/html/rfc6960#section-4.2.1
     *
     * @var array
     */
    public $ResponseBytes;

    /**
     * @see https://tools.ietf.org/html/rfc6960#section-4.2.1
     *
     * @var array
     */
    public $ResponseData;

    /**
     * @see https://tools.ietf.org/html/rfc6960#section-4.2.1
     *
     * @var array
     */
    public $RevokedInfo;

    /**
     * @see https://tools.ietf.org/html/rfc6960#section-4.2.1
     * @see https://tools.ietf.org/html/rfc6960#section-4.2.2.3
     *
     * @var array
     */
    public $SingleResponse;

    /**
     * @see https://tools.ietf.org/html/rfc6960#section-4.1.1
     *
     * @var array
     */
    public $Version;

    /**
     * @param X509Asn1|null $x509Asn1
     */
    public function __construct(X509Asn1 $x509Asn1 = null)
    {
        $x509Asn1 = $x509Asn1 ?: new X509Asn1();

        $this->OCSPResponseStatus = array(
            'type' => BaseAsn1::TYPE_ENUMERATED,
            'mapping' => array(
                self::OCSP_SUCCESSFUL => self::OCSP_SUCCESSFUL,
                self::OCSP_MALFORMED_REQUEST => self::OCSP_MALFORMED_REQUEST,
                self::OCSP_INTERNAL_ERROR => self::OCSP_INTERNAL_ERROR,
                self::OCSP_TRY_LATER => self::OCSP_TRY_LATER,
                self::OCSP_SIG_REQUIRED => self::OCSP_SIG_REQUIRED,
                self::OCSP_UNAUTHORIZED => self::OCSP_UNAUTHORIZED,
            )
        );

        $this->ResponseBytes = array(
            'constant' => 0,
            'optional' => true,
            'explicit' => true,
            'type' => BaseAsn1::TYPE_SEQUENCE,
            'children' => array(
                'responseType' => array('type' => BaseAsn1::TYPE_OBJECT_IDENTIFIER),
                // The value for responseBytes consists of an OBJECT IDENTIFIER and a
                // response syntax identified by that OID encoded as an OCTET STRING.
                'response' => array('type' => BaseAsn1::TYPE_OCTET_STRING),
            )
        );

        $this->OCSPResponse = array(
            'type' => BaseAsn1::TYPE_SEQUENCE,
            'children' => array(
                'responseStatus' => $this->OCSPResponseStatus,
                'responseBytes' => $this->ResponseBytes
            ),
        );

        $this->RevokedInfo = array(
            'type' => BaseAsn1::TYPE_SEQUENCE,
            'children' => array(
                'revocationTime' => array('type' => BaseAsn1::TYPE_GENERALIZED_TIME),
                'revocationReason' => $x509Asn1->CRLReason + array(
                    'constant' => 0,
                    'explicit' => true,
                    'optional' => true,
                ),
            ),
        );

        $this->CertStatus = array(
            'type' => BaseAsn1::TYPE_CHOICE,
            'children' => array(
                'good' => array(
                    'type' => BaseAsn1::TYPE_NULL,
                    'constant' => 0,
                    'implicit' => true,
                ),
                'revoked' => $this->RevokedInfo + array(
                    'constant' => 1,
                    'implicit' => true
                ),
                'unknown' => array(
                    'constant' => 2,
                    'implicit' => true,
                    'type' => BaseAsn1::TYPE_NULL,
                ),
            ),
        );

        $this->Version = array(
            'type' => BaseAsn1::TYPE_INTEGER,
            'mapping' => array(0 => 'v1'),
        );

        $this->ResponderID = array(
            'type' => BaseAsn1::TYPE_CHOICE,
            'children' => array(
                // Added 'explicit' => true - otherwise the parser breaks.
                'byName' => $x509Asn1->Name + array('constant' => 1, 'explicit' => true),
                'byKey' => array(
                    'constant' => 2,
                    // SHA-1 hash of responder's public key
                    'type' => BaseAsn1::TYPE_OCTET_STRING,
                ),
            ),
        );

        $this->CertID = array(
            'type' => BaseAsn1::TYPE_SEQUENCE,
            'children' => array(
                'hashAlgorithm' => $x509Asn1->AlgorithmIdentifier,
                'issuerNameHash' => array('type' => BaseAsn1::TYPE_OCTET_STRING),
                'issuerKeyHash' => array('type' => BaseAsn1::TYPE_OCTET_STRING),
                'serialNumber' => $x509Asn1->CertificateSerialNumber,
            ),
        );

        $this->SingleResponse = array(
            'type' => BaseAsn1::TYPE_SEQUENCE,
            'children' => array(
                'certID' => $this->CertID,
                'certStatus' => $this->CertStatus,
                'thisUpdate' => array(
                    'type' => BaseAsn1::TYPE_GENERALIZED_TIME,
                ),
                'nextUpdate' => array(
                    'type' => BaseAsn1::TYPE_GENERALIZED_TIME,
                    'constant' => 0,
                    'explicit' => true,
                    'optional' => true,
                ),
                'singleExtensions' => $x509Asn1->Extensions + array(
                    'constant' => 1,
                    'explicit' => true,
                    'optional' => true,
                ),
            ),
        );

        $this->ResponseData = array(
            'type' => BaseAsn1::TYPE_SEQUENCE,
            'children' => array(
                'version' => $this->Version + array(
                    'explicit' => true,
                    'constant' => 0,
                    'default' => 'v1'
                ),
                'responderID' => $this->ResponderID,
                'producedAt' => array(
                    'type' => BaseAsn1::TYPE_GENERALIZED_TIME
                ),
                'responses' => array(
                    'type' => BaseAsn1::TYPE_SEQUENCE,
                    'min' => 0,
                    'max' => -1,
                    'children' => $this->SingleResponse
                ),
                'responseExtensions' => $x509Asn1->Extensions + array(
                    'explicit' => true,
                    'constant' => 1,
                    'optional' => true,
                ),
            ),
        );

        $this->BasicOCSPResponse = array(
            'type' => BaseAsn1::TYPE_SEQUENCE,
            'children' => array(
                'tbsResponseData' => $this->ResponseData,
                'signatureAlgorithm' => $x509Asn1->AlgorithmIdentifier,
                'signature' => array(
                    'type' => BaseAsn1::TYPE_BIT_STRING
                ),
                'certs' => array(
                    'constant' => 0,
                    'explicit' => true,
                    'optional' => true,
                    'min' => 0,
                    'max' => -1,
                    'children' => $x509Asn1->Certificate,
                ),
            ),
        );
    }
}
