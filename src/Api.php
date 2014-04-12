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

use KG\DigiDoc\Exception\RuntimeException;
use Symfony\Component\HttpFoundation\File\File;

/**
 * The central point through which all the communication with the DigiDoc
 * service flows.
 */
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
     * @param \SoapClient $client
     */
    public function __construct(\SoapClient $client)
    {
        $this->client = $client;
    }

    /**
     * Opens a new session with the DigiDoc service.
     *
     * @todo Handle exceptions
     *
     * @param File|null $file
     *
     * @return Session
     */
    public function openSession(File $file = null)
    {
        if ($file) {
            $contents = $this->base64Encode($this->getFileContents($file));
        } else {
            $contents = '';
        }

        list($status, $sessionId) = array_values(
            $this->client->__soapCall('StartSession', array('', $contents, true, ''))
        );

        return new Session($sessionId);
    }

    /**
     * Creates a new DigiDoc container.
     *
     * @param Session $session
     */
    public function createContainer(Session $session)
    {
        $this->client->__soapCall('CreateSignedDoc', array($session->getId(), self::DOC_FORMAT, self::DOC_VERSION));
    }

    /**
     * Adds a new file to the given session
     *
     * @param Session $session
     * @param File    $file
     */
    public function addFile(Session $session, File $file)
    {
        $response = $this->client->__soapCall('AddDataFile', array(
            $session->getId(),
            $file->getFileName(),
            $file->getMimeType(),
            self::CONTENT_TYPE_EMBEDDED,
            $file->getSize(),
            '',
            '',
            $this->base64Encode($this->getFileContents($file)),
        ));
    }

    /**
     * @param Session     $session
     * @param Certificate $certificate
     *
     * @return Signature
     */
    public function createSignature(Session $session, Certificate $certificate)
    {
        list(, $signatureId, $challenge) = array_values($this->client->__soapCall('PrepareSignature', array(
            $session->getId(),
            $certificate->getCertificate(),
            $certificate->getId()
        )));

        return new Signature($this, $session, $signatureId, $challenge);
    }

    /**
     * Finalizes the signature, effectively making it valid.
     *
     * @param Session   $session
     * @param Signature $signature
     * @param string    $solution
     *
     * @return boolean Whether the signature was successfully finished
     */
    public function finishSignature(Session $session, Signature $signature, $solution)
    {
        list(, $info) = array_values($this->client->__soapCall('FinalizeSignature', array(
            $session->getId(),
            $signature->getId(),
            $solution
        )));

        if ($this->isSignatureValid($signature, $info->SignatureInfo)) {
            return true;
        }

        // Removes the added invalid signature to preserve atomicity.
        $this->removeSignature($session, $signature);

        return false;
    }

    /**
     * Removes the given signature.
     *
     * @param Session   $session
     * @param Signature $signature
     */
    public function removeSignature(Session $session, Signature $signature)
    {
        $this->client->__soapCall('RemoveSignature', array(
            $session->getId(),
            $signature->getId()
        ));
    }

    /**
     * Retrieves the contents of the opened file from the server.
     *
     * @param Session $session
     *
     * @return string
     */
    public function getContents(Session $session)
    {
        list(, $contents) = array_values($this->client->__soapCall('GetSignedDoc', array($session->getId())));

        return $this->base64Decode($contents);
    }

    /**
     * Closes the given session with the DigiDoc service.
     *
     * @param Session $session
     */
    public function closeSession(Session $session)
    {
        $this->client->__soapCall('CloseSession', array($session->getId()));
    }

    /**
     * Checks whether the signature with the given signature id is valid.
     *
     * @param Signature    $signature
     * @param array|object $signatures
     *
     * @return boolean
     */
    private function isSignatureValid(Signature $signature, $signatures)
    {
        if (!is_array($signatures)) {
            $signatures = array($signatures);
        }

        foreach ($signatures as $addedSignature) {
            if ($addedSignature->Id === $signature->getId()) {
                return 'OK' === $addedSignature->Status;
            }
        }

        return false;
    }

    /**
     * Gets the contents of the file.
     *
     * @todo This is almost a duplicate of FileContainer::getContents(),
     *       refactor this out.
     *
     * @param File $file
     *
     * @return string
     */
    private function getFileContents(File $file)
    {
        $level = error_reporting(0);
        $contents = file_get_contents($file->getPathname());
        error_reporting($level);

        if (false === $contents) {
            $error = error_get_last();
            throw new RuntimeException($error['message']);
        }

        return $contents;
    }

    /**
     * Base64 encodes a string to the required format - split into 64 bytes
     * delimited by newline characters.
     *
     * @param string $data
     *
     * @return string
     */
    private function base64Encode($data)
    {
        return chunk_split(base64_encode($data), 64, "\n");
    }


    /**
     * Decodes a piece of data from base64. The encoded data may be either
     * a long string in base64 or delimited by newline characters.
     *
     * @param string $data The encoded data
     *
     * @return string
     */
    private function base64Decode($data)
    {
        $decoded    = '';
        $delimiters = "\n";
        $token      = strtok($data, $delimiters);

        while (false !== $token) {
            $decoded .= base64_decode($token);

            $token = strtok($delimiters);
        }

        return $decoded;
    }
}
