<?php

/*
 * This file is part of the DigiDoc package.
 *
 * (c) Kristen Gilden <kristen.gilden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KG\DigiDoc;

use KG\DigiDoc\Exception\ApiException;
use KG\DigiDoc\Exception\RuntimeException;
use KG\DigiDoc\Soap\Wsdl\SignedDocInfo;

class Api implements ApiInterface
{
    const CONTENT_TYPE_HASHCODE = 'HASHCODE';
    const CONTENT_TYPE_EMBEDDED = 'EMBEDDED_BASE64';

    const DOC_FORMAT = 'BDOC';
    const DOC_VERSION = '2.1';

    /**
     * @var \SoapClient
     */
    private $client;

    /**
     * @var Encoder
     */
    private $encoder;

    /**
     * @var Tracker
     */
    private $tracker;

    /**
     * @param \SoapClient  $client
     * @param Encoder|null $encoder
     * @param Tracker|null $tracker
     */
    public function __construct(\SoapClient $client, Encoder $encoder = null, Tracker $tracker = null)
    {
        $this->client = $client;
        $this->encoder = $encoder ?: new Encoder();
        $this->tracker = $tracker ?: new Tracker();
    }

    /**
     * {@inheritDoc}
     */
    public function create()
    {
        $result = $this->call('startSession', array('', '', true, ''));
        $result = $this->call('createSignedDoc', array($sessionId = $result['Sesscode'], self::DOC_FORMAT, self::DOC_VERSION));

        $envelope = new Envelope(new Session($sessionId));

        $this->tracker->add($envelope);

        return $envelope;
    }

    /**
     * {@inheritDoc}
     */
    public function fromString($bytes)
    {
        $result = $this->call('startSession', array('', $this->encoder->encode($bytes), true, ''));

        return $this->createEnvelope($result['Sesscode'], $result['SignedDocInfo']);
    }

    /**
     * {@inheritDoc}
     */
    public function open($path)
    {
        $result = $this->call('startSession', array('', $this->encoder->encodeFileContent($path), true, ''));

        return $this->createEnvelope($result['Sesscode'], $result['SignedDocInfo']);
    }

    /**
     * Creates a new DigiDoc envelope.
     *
     * @param string        $sessionCode
     * @param SignedDocInfo $signedDocInfo
     *
     * @return Envelope
     */
    private function createEnvelope($sessionCode, SignedDocInfo $signedDocInfo)
    {
        $envelope = new Envelope(
            new Session($sessionCode),
            $this->createAndTrack($signedDocInfo->DataFileInfo, 'KG\DigiDoc\File'),
            $this->createAndTrack($signedDocInfo->SignatureInfo, 'KG\DigiDoc\Signature')
        );

        $this->tracker->add($envelope);

        return $envelope;
    }

    /**
     * {@inheritDoc}
     */
    public function close(Envelope $envelope)
    {
        $this->call('closeSession', array($envelope->getSession()->getId()));
    }

    /**
     * {@inheritDoc}
     *
     * @param boolean $merge Merges the envelope before updating it (false by default)
     */
    public function update(Envelope $envelope, $merge = false)
    {
        if ($merge) {
            $this->merge($envelope);
        } else {
            $this->failIfNotMerged($envelope);
        }

        $session = $envelope->getSession();

        $this
            ->addFiles($session, $envelope->getFiles())
            ->addSignatures($session, $envelope->getSignatures())
            ->sealSignatures($session, $envelope->getSignatures())
        ;
    }

    /**
     * {@inheritDoc}
     */
    public function toString(Envelope $envelope)
    {
        $this->failIfNotMerged($envelope);

        $result = $this->call('getSignedDoc', array($envelope->getSession()->getId()));

        return $this->encoder->decode($result['SignedDocData']);
    }

    /**
     * {@inheritDoc}
     */
    public function write(Envelope $envelope, $path)
    {
        file_put_contents($path, $this->toString($envelope));
    }

    /**
     * {@inheritDoc}
     */
    public function merge(Envelope $envelope)
    {
        if ($this->tracker->has($envelope)) {
            return;
        }

        $this->tracker->add($envelope);
        $this->tracker->add($envelope->getFiles()->toArray());
        $this->tracker->add($envelope->getSignatures()->toArray());
    }

    private function addFiles(Session $session, $files)
    {
        foreach ($files as $file) {
            // Skips already tracked files, because they're already added.
            if ($this->tracker->has($file)) {
                continue;
            }

            $this->call('addDataFile', array(
                $session->getId(),
                $file->getName(),
                $file->getMimeType(),
                self::CONTENT_TYPE_EMBEDDED,
                $file->getSize(),
                '',
                '',
                $this->encoder->encode($file->getContent()),
            ));

            $this->tracker->add($file);
        }

        return $this;
    }

    private function addSignatures(Session $session, $signatures)
    {
        foreach ($signatures as $signature) {
            // Skips already tracked signatures, because they're already added.
            if ($this->tracker->has($signature)) {
                continue;
            }

            $result = $this->call('prepareSignature', array($session->getId(), $signature->getCertSignature(), $signature->getCertId()));

            $signature->setId($result['SignatureId']);
            $signature->setChallenge($result['SignedInfoDigest']);

            $this->tracker->add($signature);
        }

        return $this;
    }

    private function sealSignatures(Session $session, $signatures)
    {
        foreach ($signatures as $signature) {
            // Skips already sealed signatures.
            if ($signature->isSealed()) {
                continue;
            }

            // Skips signatures without a solution.
            if (!$signature->getSolution()) {
                continue;
            }

            $result = $this->call('finalizeSignature', array($session->getId(), $signature->getId(), $signature->getSolution()));

            $signature->seal();
        }

        return $this;
    }

    private function getById($remoteObjects, $id)
    {
        $remoteObjects = !is_array($remoteObjects) ? array($remoteObjects) : $remoteObjects;

        foreach ($remoteObjects as $remoteObject) {
            if ($remoteObject->Id === $id) {
                return $remoteObject;
            }
        }

        throw new RuntimeException(sprintf('No remote object with id "%s" was not found.', $id));
    }

    private function createAndTrack($remoteObjects, $class)
    {
        if (is_null($remoteObjects)) {
            return array();
        }

        $remoteObjects = !is_array($remoteObjects) ? array($remoteObjects) : $remoteObjects;

        $objects = array();

        foreach ($remoteObjects as $remoteObject) {
            $objects[] = $object = $class::createFromSoap($remoteObject);

            $this->tracker->add($object);
        }


        return $objects;
    }

    private function call($method, array $arguments)
    {
        return $this->client->__soapCall(ucfirst($method), $arguments);
    }

    /**
     * @param Envelope $envelope
     *
     * @throws ApiException If the DigiDoc envelope is not merged
     */
    private function failIfNotMerged(Envelope $envelope)
    {
        if (!$this->tracker->has($envelope)) {
            throw ApiException::createNotMerged($envelope);
        }
    }
}
