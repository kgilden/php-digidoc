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
use KG\DigiDoc\Collections\FileCollection;
use KG\DigiDoc\Collections\SignatureCollection;
use KG\DigiDoc\Soap\Wsdl\SignedDocInfo;

class Api
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
     * @param \SoapClient $client
     * @param Encoder
     */
    public function __construct(\SoapClient $client, Encoder $encoder, Tracker $tracker)
    {
        $this->client = $client;
        $this->encoder = $encoder;
        $this->tracker = $tracker;
    }

    /**
     * Creates a new archive.
     *
     * @api
     *
     * @return Archive
     */
    public function create()
    {
        $result = $this->call('startSession', ['', '', true, '']);
        $result = $this->call('createSignedDoc', [$sessionId = $result['Sesscode'], self::DOC_FORMAT, self::DOC_VERSION]);

        $archive = new Archive(new Session($sessionId));

        $this->tracker->track($archive);

        return $archive;
    }

    /**
     * Opens an archive on the local filesystem.
     *
     * @api
     *
     * @param string $path Path to the archive
     *
     * @return Archive
     */
    public function open($path)
    {
        $result = $this->call('startSession', ['', $this->encoder->encode($this->getFileContent($path)), true, '']);

        $archive = new Archive(
            new Session($result['Sesscode']),
            new FileCollection($this->createAndTrack($result['SignedDocInfo']->DataFileInfo, 'KG\DigiDoc\File')),
            new SignatureCollection($this->createAndTrack($result['SignedDocInfo']->SignatureInfo, 'KG\DigiDoc\Signature'))
        );

        $this->tracker->track($archive);

        return $archive;
    }

    /**
     * Closes the session between the local and remote systems of the given
     * archive. This must be the last method called after all other
     * transactions.
     *
     * @api
     *
     * @param Archive $archive
     */
    public function close(Archive $archive)
    {
        $this->call('closeSession', [$archive->getSession()->getId()]);
    }

    /**
     * Updates the archive in the remote api to match the contents of the given
     * archive. The following is done in the same order:
     *
     *  - new files uploaded;
     *  - new signatures added and challenges injected;
     *  - signatures with solutions to challenges sealed;
     *
     * @api
     *
     * @param Archive $archive
     */
    public function update(Archive $archive)
    {
        $this->failIfNotMerged($archive);

        $session = $archive->getSession();

        $this->addFiles($session, $this->tracker->filterUntracked($archive->getFiles()));
        $this->addSignatures($session, $this->tracker->filterUntracked($archive->getSignatures()));
        $this->sealSignatures($session, $archive->getSignatures()->getSealable());
    }

    /**
     * Downloads the contents of the archive from the server and writes them
     * to the given local path. If you modify an archive and call this method
     * without prior updating, the changes will not be reflected in the written
     * file.
     *
     * @api
     *
     * @param Archive $archive
     * @param string  $path
     */
    public function write(Archive $archive, $path)
    {
        $this->failIfNotMerged($archive);

        $result = $this->call('getSignedDoc', [$archive->getSession()->getId()]);

        file_put_contents($path, $this->encoder->decode($result['SignedDocData']));
    }

    /**
     * Merges the archive back with the api. This is necessary, when working
     * with an archive over multiple requests and storing the archive somewhere
     * (session, database etc) in the meantime.
     *
     * @param Archive $archive
     */
    public function merge(Archive $archive)
    {
        if ($this->tracker->isTracked($archive)) {
            return;
        }

        $this->tracker->track($archive);
        $this->tracker->trackMultiple($archive->getFiles()->toArray());
        $this->tracker->trackMultiple($archive->getSignatures()->toArray());
    }

    private function addFiles(Session $session, FileCollection $files)
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
                $this->encoder->encode($this->getFileContent($file->getPathname())),
            ]);

            $this->tracker->track($file);
        }
    }

    private function addSignatures(Session $session, SignatureCollection $signatures)
    {
        foreach ($signatures as $signature) {
            $result = $this->call('prepareSignature', [$session->getId(), $signature->getCertificate()->getCertificate(), $signature->getCertificate()->getId()]);

            $signature->setId($result['SignatureId']);
            $signature->setChallenge($result['SignedInfoDigest']);

            $this->tracker->track($signature);
        }
    }

    private function sealSignatures(Session $session, SignatureCollection $signatures)
    {
        foreach ($signatures as $signature) {
            $result = $this->call('finalizeSignature', [$session->getId(), $signature->getId(), $signature->getSolution()]);

            $signature->seal();
        }
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

            $this->tracker->track($object);
        }


        return $objects;
    }

    private function call($method, array $arguments)
    {
        return $this->client->__soapCall(ucfirst($method), $arguments);
    }

    /**
     * @param Archive $archive
     *
     * @throws ApiException If the archive is not merged
     */
    private function failIfNotMerged(Archive $archive)
    {
        if (!$this->tracker->isTracked($archive)) {
            throw ApiException::createNotTracked($archive);
        }
    }

    /**
     * Gets the file content.
     *
     * @todo Refactor this out to some other class
     *
     * @param string $pathToFile
     *
     * @return string
     */
    private function getFileContent($pathToFile)
    {
        $level = error_reporting(0);
        $content = file_get_contents($pathToFile);
        error_reporting($level);

        if (false === $content) {
            $error = error_get_last();
            throw new RuntimeException($error['message']);
        }

        return $content;
    }
}
