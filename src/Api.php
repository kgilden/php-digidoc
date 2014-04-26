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
        $result = $this->call('startSession', ['', '', true, '']);
        $result = $this->call('createSignedDoc', [$sessionId = $result['Sesscode'], self::DOC_FORMAT, self::DOC_VERSION]);

        $container = new Container(new Session($sessionId));

        $this->tracker->add($container);

        return $container;
    }

    /**
     * {@inheritDoc}
     */
    public function open($path)
    {
        $result = $this->call('startSession', ['', $this->encoder->encodeFileContent($path), true, '']);

        $container = new Container(
            new Session($result['Sesscode']),
            $this->createAndTrack($result['SignedDocInfo']->DataFileInfo, 'KG\DigiDoc\File'),
            $this->createAndTrack($result['SignedDocInfo']->SignatureInfo, 'KG\DigiDoc\Signature')
        );

        $this->tracker->add($container);

        return $container;
    }

    /**
     * {@inheritDoc}
     */
    public function close(Container $container)
    {
        $this->call('closeSession', [$container->getSession()->getId()]);
    }

    /**
     * {@inheritDoc}
     */
    public function update(Container $container)
    {
        $this->failIfNotMerged($container);

        $session = $container->getSession();
        $tracker = $this->tracker;

        $this
            ->addFiles($session, $container->getFiles()->filter($untrackedFn))
            ->addSignatures($session, $container->getSignatures())
            ->sealSignatures($session, $container->getSignatures())
        ;
    }

    /**
     * {@inheritDoc}
     */
    public function write(Container $container, $path)
    {
        $this->failIfNotMerged($container);

        $result = $this->call('getSignedDoc', [$container->getSession()->getId()]);

        file_put_contents($path, $this->encoder->decode($result['SignedDocData']));
    }

    /**
     * {@inheritDoc}
     */
    public function merge(Container $container)
    {
        if ($this->tracker->has($container)) {
            return;
        }

        $this->tracker->add($container);
        $this->tracker->add($container->getFiles()->toArray());
        $this->tracker->add($container->getSignatures()->toArray());
    }

    private function addFiles(Session $session, $files)
    {
        foreach ($files as $file) {
            $this->call('addDataFile', [
                $session->getId(),
                $file->getName(),
                $file->getMimeType(),
                self::CONTENT_TYPE_EMBEDDED,
                $file->getSize(),
                '',
                '',
                $this->encoder->encodeFileContent($file->getPathname()),
            ]);

            $this->tracker->add($file);
        }

        return $this;
    }

    private function addSignatures(Session $session, $signatures)
    {
        foreach ($signatures as $signature) {
            // Skips already tracked objects, because they're already added.
            if ($tracker->has($object)) {
                continue;
            }

            $result = $this->call('prepareSignature', [$session->getId(), $signature->getCertificate()->getSignature(), $signature->getCertificate()->getId()]);

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

            $result = $this->call('finalizeSignature', [$session->getId(), $signature->getId(), $signature->getSolution()]);

            $signature->seal();
        }

        return $this;
    }

    private function getById($remoteObjects, $id)
    {
        $remoteObjects = !is_array($remoteObjects) ? [$remoteObjects] : $remoteObjects;

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
            return [];
        }

        $remoteObjects = !is_array($remoteObjects) ? [$remoteObjects] : $remoteObjects;

        $objects = [];

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
     * @param Container $container
     *
     * @throws ApiException If the DigiDoc container is not merged
     */
    private function failIfNotMerged(Container $container)
    {
        if (!$this->tracker->has($container)) {
            throw ApiException::createNotTracked($container);
        }
    }
}
