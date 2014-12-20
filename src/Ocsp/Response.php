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

use phpseclib\File\ASN1;
use phpseclib\File\X509;

/**
 * Models an OCSP response.
 */
class Response
{
    const OCSP_SUCCESSFUL = 0;
    const OCSP_MALFORMED_REQUEST = 1;
    const OCSP_INTERNAL_ERROR = 2;
    const OCSP_TRY_LATER = 3;
    const OCSP_SIG_REQUIRED = 5;
    const OCSP_UNAUTHORIZED = 6;

    const OID_OCSP_NONCE = '1.3.6.1.5.5.7.48.1.2';

    /**
     * @var string
     */
    private $content;

    /**
     * @param string $content
     */
    public function __construct($content)
    {
        $this->content = $content;
    }

    /**
     * Gets the response status.
     *
     * @return integer One of the self::OCSP_* constants
     */
    public function getStatus()
    {
        $asn1 = new ASN1();

        $responseDecoded = $asn1->decodeBER($this->getContent());
        $responseMapped = $asn1->asn1map($responseDecoded[0], $this->createOcspResponseAsn1Mapping());

        return $responseMapped['responseStatus'];
    }

    /**
     * @param string $nonce Nonce as a binary string to compare against
     *
     * @return boolean
     */
    public function isNonceEqualTo($nonce)
    {
        return $nonce === $this->getNonce();
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getContent();
    }

    /**
     * @return string Binary string of the nonce
     */
    private function getNonce()
    {
        $asn1 = new ASN1();

        $responseDecoded = $asn1->decodeBER($this->getContent());
        $responseMapped = $asn1->asn1map($responseDecoded[0], $this->createOcspResponseAsn1Mapping());

        $asn1 = new ASN1();
        // @todo make sure to check for response type too!
        $responseBasicDecoded = $asn1->decodeBER(base64_decode($responseMapped['responseBytes']['response']));
        $responseBasicMapped = $asn1->asn1map($responseBasicDecoded[0], $this->createBasicOcspResponseAsn1Mapping());

        foreach ($responseBasicMapped['tbsResponseData']['responseExtensions'] as $extension) {
            if (self::OID_OCSP_NONCE === $extension['extnId']) {
                return base64_decode($extension['extnValue']);
            }
        }

        throw new \RuntimeExcetpion('The response does not contain a nonce.');
    }


    /**
     * A minimal ASN1 mapping for an OCSP response as specified in
     * {@link https://tools.ietf.org/html/rfc2560#page-8 [RFC2560]}.
     *
     * @return array
     */
    private function createOcspResponseAsn1Mapping()
    {
        $ocspResponseStatus = array(
            'type' => ASN1::TYPE_ENUMERATED,
            'mapping' => array(
                self::OCSP_SUCCESSFUL => self::OCSP_SUCCESSFUL,
                self::OCSP_MALFORMED_REQUEST => self::OCSP_MALFORMED_REQUEST,
                self::OCSP_INTERNAL_ERROR => self::OCSP_INTERNAL_ERROR,
                self::OCSP_TRY_LATER => self::OCSP_TRY_LATER,
                self::OCSP_SIG_REQUIRED => self::OCSP_SIG_REQUIRED,
                self::OCSP_UNAUTHORIZED => self::OCSP_UNAUTHORIZED,
            )
        );

        $responseBytes = array(
            'constant' => 0,
            'optional' => true,
            'explicit' => true,
            'type' => ASN1::TYPE_SEQUENCE,
            'children' => array(
                'responseType' => array('type' => ASN1::TYPE_OBJECT_IDENTIFIER),
                // The value for responseBytes consists of an OBJECT IDENTIFIER and a
                // response syntax identified by that OID encoded as an OCTET STRING.
                'response' => array('type' => ASN1::TYPE_OCTET_STRING),
            )
        );

        $ocspResponse = array(
            'type' => ASN1::TYPE_SEQUENCE,
            'children' => array(
                'responseStatus' => $ocspResponseStatus,
                'responseBytes' => $responseBytes
            ),
        );

        return $ocspResponse;
    }

    /**
     * ASN1 mapping for responses of the id-pkix-ocsp-basic response type as
     * specified in {@link https://tools.ietf.org/html/rfc2560#page-9 [RFC2560]}.
     *
     * @return array
     */
    private function createBasicOcspResponseAsn1Mapping()
    {
        $x509 = new X509();

        // These ASN1 constructs are copied from X509.
        $AlgorithmIdentifier = array(
            'type' => ASN1::TYPE_SEQUENCE,
            'children' => array(
                'algorithm'  => array('type' => ASN1::TYPE_OBJECT_IDENTIFIER),
                'parameters' => array(
                    'type' => ASN1::TYPE_ANY,
                    'optional' => true,
                ),
            ),
        );

        $CertificateSerialNumber = array('type' => ASN1::TYPE_INTEGER);
        // End of copied ASN1 constructs.

        $revokedInfo = array(
            'revocationTime' => array('type' => ASN1::TYPE_GENERALIZED_TIME),
            'revocationReason' => $x509->CRLReason + array(
                'constant' => 0,
                'explicit' => true,
                'optional' => true,
            ),
        );

        $certStatus = array(
            'type' => ASN1::TYPE_CHOICE,
            'children' => array(
                'good' => array(
                    'type' => ASN1::TYPE_NULL,
                    'constant' => 0,
                    'implicit' => true,
                ),
                'revoked' => array(
                    'type' => ASN1::TYPE_SEQUENCE,
                    'constant' => 1,
                    'implicit' => true,
                    'children' => array(
                        'revocationTime' => array('type' => ASN1::TYPE_GENERALIZED_TIME),
                        'revocationReason'
                    ),
                ),
                'unknown' => '',
            ),
        );

        $version = array(
            'type' => ASN1::TYPE_INTEGER,
            'mapping' => array(0 => 'v1'),
        );

        $responderId = array(
            'type' => ASN1::TYPE_CHOICE,
            'children' => array(
                // Added 'explicit' => true - otherwise the parser breaks.
                'byName' => $x509->Name + array('constant' => 1, 'explicit' => true),
                'byKey' => array(
                    'constant' => 2,
                    'type' => ASN1::TYPE_OCTET_STRING, // SHA-1 hash of responder's public key
                ),
            ),
        );

        $certId = array(
            'type' => ASN1::TYPE_SEQUENCE,
            'children' => array(
                'hashAlgorithm' => $AlgorithmIdentifier,
                'issuerNameHash' => array('type' => ASN1::TYPE_OCTET_STRING), // Hash of Issuer's DN
                'issuerKeyHash' => array('type' => ASN1::TYPE_OCTET_STRING), // Hash of Issuer's public key
                'serialNumber' => $CertificateSerialNumber,
            )
        );

        $singleResponse = array(
            'type' => ASN1::TYPE_SEQUENCE,
            'children' => array(
                'certID' => $certId,
                'certStatus' => $certStatus,
                'thisUpdate' => array('type' => ASN1::TYPE_GENERALIZED_TIME),
            ),
        );

        $revokedInfo = array(
            'type' => ASN1::TYPE_SEQUENCE,
            'children' => array(
                'revocationTime' => array('type' => ASN1::TYPE_GENERALIZED_TIME),
                'revocationReason' => $x509->CRLReason + array(
                    'constant' => 0,
                    'explicit' => true,
                    'optional' => true,
                ),
            ),
        );

        $certStatus = array(
            'type' => ASN1::TYPE_CHOICE,
            'children' => array(
                'good' => array(
                    'type' => ASN1::TYPE_NULL,
                    'constant' => 0,
                    'implicit' => true,
                ),
                'revoked' => $revokedInfo + array(
                    'constant' => 1,
                    'implicit' => true,
                ),
                'unknown' => array(
                    'constant' => 2,
                    'implicit' => true,
                    'type' => ASN1::TYPE_NULL,
                ),
            ),
        );

        $tbsResponseData = array(
            'type' => ASN1::TYPE_SEQUENCE,
            'children' => array(
                'version' => $version + array(
                    'explicit' => true,
                    'constant' => 0,
                    'default' => 'v1'
                ),
                'responderID' => $responderId,
                'producedAt' => array('type' => ASN1::TYPE_GENERALIZED_TIME),
                'responses' => array('type' => ASN1::TYPE_SEQUENCE, 'min' => 0, 'max' => -1, 'children' => $singleResponse),
                'responseExtensions' => $x509->Extensions + array(
                    'explicit' => true,
                    'constant' => 1,
                    'optional' => true,
                ),
            ),
        );

        $basicOcspResponse = array(
            'type' => ASN1::TYPE_SEQUENCE,
            'children' => array(
                'tbsResponseData' => $tbsResponseData,
                'signatureAlgorithm' => $AlgorithmIdentifier,
                'signature' => array('type' => ASN1::TYPE_BIT_STRING),
                'certs' => array(
                    'constant' => 0,
                    'explicit' => true,
                    'optional' => true,
                    'min' => 0,
                    'max' => -1,
                    'children' => $x509->Certificate,
                ),
            ),
        );

        return $basicOcspResponse;
    }
}
