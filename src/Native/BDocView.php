<?php

/*
 * This file is part of the DigiDoc package.
 *
 * (c) Kristen Gilden <kristen.gilden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KG\DigiDoc\Native;

use Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesser;

class BDocView
{
    const XPATH_FILE_REFS = '/asic:XAdESSignatures/ds:Signature/ds:SignedInfo';
    const XPATH_FILE_FORMATS = '/asic:XAdESSignatures/ds:Signature/xades:SignedDataObjectProperties';
    const XPATH_SIGNATURE = '/asic:XAdESSignatures/ds:Signature/ds:SignatureValue';
    const XPATH_DATA_TO_SIGN = '/asic:XAdESSignatures/ds:Signature/ds:SignedProperties';

    private $dom;

    private $xpath;

    public function __construct()
    {
        $this->dom = new \DomDocument();
        $this->dom->load(__DIR__ . '/stamp.xml');

        $this->xpath = new \DOMXPath($this->dom);
    }

    public function getDataToSign()
    {
        // @todo what if none or too many nodes found?
        return $this->xpath->query(self::XPATH_DATA_TO_SIGN)->item(0)->c14n();
    }

    public function addSignature($signature)
    {
        // @todo what if none or too many nodes found?
        // @todo algorithm is hard-coded
        $element = $this->xpath->query(self::XPATH_SIGNATURE)->item(0);
        $element->nodeValue = chunk_split(base64_encode(hash('sha256', $signature, true)), 64, "\n");
        $element->setAttribute('Id', $signatureId = uniqid());

        return $signatureId;
    }

    public function addFileDigests($files)
    {
        foreach ($files as $pathInEnvelope => $pathToFile) {
            $this->addFileDigest($pathToFile, $pathInEnvelope);
        }
    }

    public function addFileDigest($pathToFile, $pathInEnvelope)
    {
        $refId = $this->addFileReference($pathToFile, $pathInEnvelope);

        $this->addMimeType($pathToFile, $refId);
    }

    public function __toString()
    {
        throw new \Exception('Implement me!');
    }

    private function addFileReference($pathToFile, $pathInEnvelope)
    {
        // @todo what if none or too many nodes found?
        $refParent = $this->xpath->query(self::XPATH_FILE_REFS)->item(0);

        // @todo algorithm is hard-coded
        $digestMethod = $this->dom
            ->createElementNS($refParent->namespaceURI, $refParent->prefix . ':DigestMethod')
            ->setAttribute('Algorithm', 'http://www.w3.org/2001/04/xmlenc#sha256')
        ;

        $digestValue = $this->dom->createElementNS(
            $refParent->namespaceURI,
            $refParent->prefix . ':DigestValue',
            chunk_split(base64_encode(hash_file('sha256', $pathToFile, true)), 64, "\n")
        );

        $ref = $this->dom
            ->createElementNS($refParent->namespaceURI, $refParent->prefix . ':Reference')
            ->setAttribute('Id', $refId = uniqid())
            ->setAttribute('URI', $pathInEnvelope)
            ->appendChild($digestMethod)
            ->appendChild($digestValue)
        ;

        $refParent->appendChild($ref);

        return $refId;
    }

    private function addMimeType($pathToFile, $refId)
    {
        // @todo what if none or too many nodes found?
        $parent = $this->xpath->query(self::XPATH_FILE_FORMATS)->item(0);

        $mimeType = $this->dom->createElementNS(
            $parent->namespaceURI,
            $parent->prefix . ':MimeType',
            MimeTypeGuesser::getInstance()->guess($pathToFile) ?: 'application/octet-stream'
        );

        $format = $this->dom
            ->createElementNS($parent->namespaceURI, $parent->prefix . ':DataObjectFormat')
            ->setAttribute('ObjectReference', '#' . $refId)
            ->appendChild($mimeType)
        ;

        $parent->appendChild($format);
    }
}
