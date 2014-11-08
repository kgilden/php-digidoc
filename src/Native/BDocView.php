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
    const XPATH_FILE_FORMATS = '/asic:XAdESSignatures/ds:Signature//xades:SignedDataObjectProperties';
    const XPATH_SIGNATURE = '/asic:XAdESSignatures/ds:Signature/ds:SignatureValue';
    const XPATH_DATA_TO_SIGN = '/asic:XAdESSignatures/ds:Signature//xades:SignedProperties';
    const XPATH_SIGNER_CERT = '/asic:XAdESSignatures/ds:Signature/ds:KeyInfo//ds:X509Certificate';
    const XPATH_SIGNER_CERT_DIGEST = '/asic:XAdESSignatures//xades:CertDigest';
    const XPATH_SIGNER_ROLE = '/asic:XAdESSignatures//xades:ClaimedRole';
    const XPATH_SIGNATURE_LOCATION = '/asic:XAdESSignatures//xades:SignatureProductionPlace';
    const XPATH_SIGNATURE_TIMESTAMP = '/asic:XAdESSignatures//xades:SigningTime';

    private $dom;

    private $xpath;

    public function __construct(\DomDocument $dom)
    {
        $this->dom = $dom;
        $this->xpath = new \DOMXPath($this->dom);
    }

    public static function fromSignerAndFiles(Signer $signer, array $files)
    {
        $dom = new \DomDocument();
        $dom->load(__DIR__ . '/stamp.xml');

        $view = new static($dom);
        $view->setSigner($signer);
        $view->setTimestamp(new \DateTime());
        $view->addFileDigests($files);

        return $view;
    }

    /**
     * Sets the time when the signature was given.
     *
     * @todo make sure the document hasn't been signed yet
     *
     * @param \DateTime $time
     */
    public function setTimestamp(\DateTime $timestamp)
    {
        $timestamp->setTimeZone(new \DateTimeZone('UTC'));

        // @todo what if none or too many nodes found?
        $element = $this->xpath->query(self::XPATH_SIGNATURE_TIMESTAMP)->item(0);
        $element->nodeValue = $timestamp->format(\DateTime::ISO8601);
    }

    public function setSigner(Signer $signer)
    {
        $certInDer = (string) $signer->getCert();

        // 1) set the certificate
        // @todo what if none or too many nodes found?
        $element = $this->xpath->query(self::XPATH_SIGNER_CERT)->item(0);
        $element->nodeValue = chunk_split(base64_encode($certInDer), 60, "\n");

        // 2) set the certificate digest
        // @todo what if none or too many nodes found?
        // @todo algorithm is hard-coded
        $element = $this->xpath->query(self::XPATH_SIGNER_CERT_DIGEST)->item(0);
        $this->appendDigest($element, 'sha256', hash('sha256', $certInDer, true));

        // 3) claimed role
        // @todo what if none or too many nodes found?
        $this->xpath->query(self::XPATH_SIGNER_ROLE)->item(0)->nodeValue = $signer->getRole();

        // 4) add the place where this signature was given
        // @todo what if none or too many nodes found?
        $location = $signer->getLocation();

        $locationByTagNames = array(
            'City' => $location->getCity(),
            'StateOrProvince' => $location->getStateOrProvince(),
            'PostalCode' => $location->getPostalCode(),
            'CountryName' => $location->getCountry(),
        );

        $element = $this->xpath->query(self::XPATH_SIGNATURE_LOCATION)->item(0);
        foreach ($locationByTagNames as $tagName => $nodeValue) {
            $element->getElementsByTagName($tagName)->item(0)->nodeValue = $nodeValue;
        }
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
        $element->nodeValue = chunk_split(base64_encode(hash('sha256', $signature, true)), 60, "\n");
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

        $ref = $this->dom->createElementNS($refParent->namespaceURI, $refParent->prefix . ':Reference');
        $ref->setAttribute('Id', $refId = uniqid());
        $ref->setAttribute('URI', $pathInEnvelope);

        // @todo algorithm is hard-coded
        $this->appendDigest($ref, 'sha256', hash_file('sha256', $pathToFile, true));

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

        $format = $this->dom->createElementNS($parent->namespaceURI, $parent->prefix . ':DataObjectFormat');
        $format->setAttribute('ObjectReference', '#' . $refId);
        $format->appendChild($mimeType);

        $parent->appendChild($format);
    }

    /**
     * @param DOMNode $parent
     * @param string  $algo
     * @param string  $digest Raw, unarmored digest
     */
    private function appendDigest(\DOMNode $parent, $algo, $digest)
    {
        $algoMap = array(
            'sha256' => 'http://www.w3.org/2001/04/xmlenc#sha256'
        );

        if (!isset($algoMap[$algo])) {
            // @todo better exception type?
            throw new \Exception(sprintf('Unsupported algo "%s", supporting "%s".', $algo, implode('", "', array_keys($algoMap))));
        }

        $digestMethod = $this->createDsElement('DigestMethod');
        $digestMethod->setAttribute('Algorithm', $algoMap[$algo]);
        $parent->appendChild($digestMethod);

        $digestValue = $this->createDsElement('DigestValue', chunk_split(base64_encode($digest), 60, "\n"));
        $parent->appendChild($digestValue);
    }

    /**
     * Creates an element under the "ds" namespace.
     *
     * @param string      $name  The tag name of the element
     * @param string|null $value The value of the element, by default an empty element will be created
     *
     * @see http://php.net/manual/en/domdocument.createelement.php
     *
     * @return \DomElement or false if an error occurred
     */
    private function createDsElement($name, $value = null)
    {
        $dsNamespaceUrl = $this->dom->getAttribute('xmlns:ds');
        $qualifiedName = 'ds:'.$name;

        return $this->dom->createElementNS($dsNamespaceUrl, $name, $value);
    }
}
