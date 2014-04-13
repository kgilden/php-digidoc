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

    const SOLUTION_LENGTH = 512;

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
     * @param File|null $file
     *
     * @return Session
     *
     * @throws ApiException If the response status is incorrect
     */
    public function openSession(File $file = null)
    {
        if ($file) {
            $contents = $this->base64Encode($this->getFileContents($file));
        } else {
            $contents = '';
        }

        list(, $sessionId) = array_values($this->call('StartSession', array('', $contents, true, '')));

        return new Session($sessionId);
    }

    /**
     * Creates a new DigiDoc container.
     *
     * @param Session $session
     *
     * @throws ApiException If the response status is incorrect
     */
    public function createContainer(Session $session)
    {
        $this->call('createSignedDoc', array($session->getId(), self::DOC_FORMAT, self::DOC_VERSION));
    }

    /**
     * Adds a new file to the given session
     *
     * @param Session $session
     * @param File    $file
     */
    public function addFile(Session $session, File $file)
    {
        $this->call('addDataFile', array(
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
        list(, $signatureId, $challenge) = array_values($this->call('prepareSignature', array(
            $session->getId(),
            $certificate->getCertificate(),
            $certificate->getId()
        )));

        return new Signature($this, $session, $certificate, $signatureId, $challenge);
    }

    /**
     * Finalizes the signature, effectively making it valid.
     *
     * @todo throw ApiException if the solution is too short BEFORE making the call.
     *
     * @param Session   $session
     * @param Signature $signature
     * @param string    $solution
     *
     * @return boolean Whether the signature was successfully finished
     */
    public function finishSignature(Session $session, Signature $signature, $solution)
    {
        if (self::SOLUTION_LENGTH !== strlen($solution)) {
            throw new ApiException(sprintf('Solution length must be "%d", got "%d".', self::SOLUTION_LENGTH, strlen($solution)));
        }

        list(, $info) = array_values($this->call('finalizeSignature', array(
            $session->getId(),
            $signature->getId(),
            $solution,
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
        $this->call('removeSignature', array($session->getId(), $signature->getId()));
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
        list(, $contents) = array_values($this->call('getSignedDoc', array($session->getId())));

        return $this->base64Decode($contents);
    }

    /**
     * Closes the given session with the DigiDoc service.
     *
     * @param Session $session
     */
    public function closeSession(Session $session)
    {
        $this->call('closeSession', array($session->getId()));
    }

    /**
     * Makes the actual call to the client and does initial status check.
     *
     * @param string $method
     * @param array  $arguments
     *
     * @return array
     */
    private function call($method, array $arguments)
    {
        $result = $this->client->__soapCall(ucfirst($method), $arguments);

        if ('OK' !== $result['Status']) {
            throw ApiException::createIncorrectStatus($result['Status']);
        }

        return $result;
    }

    /**
     * @param string $status
     *
     * @throws ApiException If the status is not "OK"
     */
    private function failIfStatusNotOk($status)
    {
        if ('OK' !== $status) {
            throw ApiException::createIncorrectStatus($status);
        }
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
