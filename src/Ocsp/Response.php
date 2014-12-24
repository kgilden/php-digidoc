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
     * @param string    $content
     * @param Asn1|null $asn1    ASN.1 mapping of OCSP
     */
    public function __construct($content, Asn1 $asn1 = null)
    {
        $this->content = $content;
        $this->asn1 = $asn1 ?: new Asn1();
    }

    /**
     * Gets the response status.
     *
     * @return integer One of the self::OCSP_* constants
     */
    public function getStatus()
    {
        $parser = new Asn1Parser();

        $responseDecoded = $parser->decodeBER($this->getContent());
        $responseMapped = $parser->asn1map($responseDecoded[0], $this->asn1->OCSPResponse);

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
        $parser = new Asn1Parser();

        $responseDecoded = $parser->decodeBER($this->getContent());
        $responseMapped = $parser->asn1map($responseDecoded[0], $this->asn1->OCSPResponse);

        $parser = new Asn1Parser();
        // @todo make sure to check for response type too!
        $responseBasicDecoded = $parser->decodeBER(base64_decode($responseMapped['responseBytes']['response']));
        $responseBasicMapped = $parser->asn1map($responseBasicDecoded[0], $this->asn1->BasicOCSPResponse);

        foreach ($responseBasicMapped['tbsResponseData']['responseExtensions'] as $extension) {
            if (Asn1::OID_OCSP_NONCE === $extension['extnId']) {
                return base64_decode($extension['extnValue']);
            }
        }

        throw new \RuntimeExcetpion('The response does not contain a nonce.');
    }
}
