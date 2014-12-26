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

use phpseclib\File\ASN1 as Asn1Parser;

/**
 * Models an OCSP response.
 */
class Response
{
    /**
     * @var Asn1
     */
    private $asn1;

    /**
     * @var string
     */
    private $content;

    /**
     * @var integer
     */
    private $status;

    /**
     * @var array
     */
    private $response;

    /**
     * @param string    $content
     * @param Asn1|null $asn1    ASN.1 mapping of OCSP
     */
    public function __construct($content, Asn1 $asn1 = null)
    {
        $this->content = $content;
        $this->asn1 = $asn1 ?: new Asn1();

        $parser = new Asn1Parser();

        $responseDecoded = $parser->decodeBER($content);
        $responseMapped = $parser->asn1map($responseDecoded[0], $this->asn1->OCSPResponse);

        $status = $responseMapped['responseStatus'];

        if ($status !== Asn1::OCSP_SUCCESSFUL) {
            // @todo create a better exception for this (also update ResponderTest).
            throw new \InvalidArgumentException(sprintf('The OCSP response status was not "successful (0)", got %d instead, see "%s" for more.', $status, 'https://tools.ietf.org/html/rfc6960#section-4.2.1'));
        }

        $bytes = $responseMapped['responseBytes'];

        if ($bytes['responseType'] !== Asn1::OID_ID_PKIX_OCSP_BASIC) {
            // @todo create a better exception for this (also update ResponderTest).
            throw new \InvalidArgumentException(sprintf('Unknown response type "%s".', $bytes['responseType']));
        }

        $this->status = $status;
        $this->response = $bytes['response'];
    }

    /**
     * Gets the response status.
     *
     * @return integer One of the self::OCSP_* constants
     */
    public function getStatus()
    {
        return $this->status;
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
        $parser = new Asn1Parser();

        $responseBasicDecoded = $parser->decodeBER(base64_decode($this->response));
        $responseBasicMapped = $parser->asn1map($responseBasicDecoded[0], $this->asn1->BasicOCSPResponse);

        foreach ($responseBasicMapped['tbsResponseData']['responseExtensions'] as $extension) {
            if (Asn1::OID_OCSP_NONCE === $extension['extnId']) {
                return base64_decode($extension['extnValue']);
            }
        }

        throw new \RuntimeExcetpion('The response does not contain a nonce.');
    }
}
