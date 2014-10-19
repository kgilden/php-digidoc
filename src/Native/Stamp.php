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

class Stamp
{
    /**
     * @var DOMDocument
     */
    private $dom;

    /**
     * @param DomDocument $dom DOM of the stamp's XML representation
     */
    public function __construct(\DOMDocument $dom = null)
    {
        $this->dom = $dom ?: $this->createDom();
    }

    /**
     * Adds a file reference to the stamp.
     *
     * @todo Don't allow adding files, if the stamp is signed
     *
     * @param string $pathInEnvelope File's relative path in the envelope
     * @param string $pathToFile     File's current path
     *
     * @return Stamp
     */
    public function addFile($pathInEnvelope, $pathToFile)
    {
        $xpath = new \DOMXpath($this->dom);
        $nodes = $xpath->query('/asic:XAdESSignatures/ds:Signature/ds:SignedInfo');

        foreach ($nodes as $node) {
            $this->appendRef($node, $pathInEnvelope, $pathToFile);
        }

        return $this;
    }

    public function getFileDigest($pathInEnvelope)
    {
        $xpath = new \DOMXpath($this->dom);
        $nodes = $xpath->query(sprintf('.//ds:Reference[@URI="%s"]', $pathInEnvelope));

        if ($nodes->length !== 1) {
            throw new \RuntimeException(sprintf('Expected to find 1 reference node with uri "%s", found %d.', $pathInEnvelope, $nodes->length));
        }

        return base64_decode(str_replace("\n", '', $nodes->item(0)->textContent));
    }

    /**
     * Appends a file reference to the stamp. Its structure is the following
     *
     *   <ds:Reference Id="__id__" URI="__pathInEnvelope__">
     *     <ds:DigestMethod Algorithm="http://www.w3.org/2001/04/xmlenc#sha256"/>
     *     <ds:DigestValue>__b64enc_digest__</ds:DigestValue>
     *   </ds:Reference>
     *
     * @param \DOMNode $parent
     * @param string   $pathInEnvelope
     * @param string   $pathToFile
     */
    private function appendRef(\DOMNode $parent, $pathInEnvelope, $pathToFile)
    {
        $prefix = $parent->prefix;
        $nsUri = $parent->namespaceURI;

        $ref = $this->dom->createElementNS($nsUri, $prefix . ':Reference');
        $ref->setAttribute('Id', uniqid());
        $ref->setAttribute('URI', $pathInEnvelope);

        $digestMethod = $this->dom->createElementNS($nsUri, $prefix . ':DigestMethod');
        $digestMethod->setAttribute('Algorithm', 'http://www.w3.org/2001/04/xmlenc#sha256');

        $digestValue = $this->dom->createElementNS($nsUri, $prefix . ':DigestValue', $this->digestFile('sha256', $pathToFile));

        $ref
            ->appendChild($digestMethod)
            ->appendChild($digestValue)
        ;

        $parent->appendChild($ref);
    }

    /**
     * @return \DOMDocument
     */
    private function createDom()
    {
        $dom = new \DOMDocument();
        $dom->load(__DIR__ . '/stamp.xml');

        return $dom;
    }

    /**
     * @param string $algo
     * @param string $path
     *
     * @return string
     */
    private function digestFile($algo, $path)
    {
        return chunk_split(base64_encode(hash_file($algo, $path, true)), 64, "\n");
    }
}
