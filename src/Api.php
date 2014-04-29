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

        $container = new Container(new Session($sessionId));

        $this->tracker->add($container);

        return $container;
    }

    /**
     * {@inheritDoc}
     */
    public function open($path)
    {
        $result = $this->call('startSession', array('', $this->encoder->encodeFileContent($path), true, ''));

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
        $this->call('closeSession', array($container->getSession()->getId()));
    }

    /**
     * {@inheritDoc}
     *
     * @param boolean $merge Merges the container before updating it (false by default)
     */
    public function update(Container $container, $merge = false)
    {
        if ($merge) {
            $this->merge($container);
        } else {
            $this->failIfNotMerged($container);
        }

        $session = $container->getSession();

        $this
            ->addFiles($session, $container->getFiles())
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

        $result = $this->call('getSignedDoc', array($container->getSession()->getId()));

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
                $this->encoder->encodeFileContent($file->getPathname()),
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
     * @param Container $container
     *
     * @throws ApiException If the DigiDoc container is not merged
     */
    private function failIfNotMerged(Container $container)
    {
        if (!$this->tracker->has($container)) {
            throw ApiException::createNotMerged($container);
        }
    }
}
