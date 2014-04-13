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
     * @var Session
     */
    private $session;

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
     * @throws ApiException If the response status is incorrect
     */
    public function openSession(File $file = null)
    {
        if ($this->isSessionOpened()) {
            throw new ApiException(sprintf('The session is already opened (id %s)', $this->getSession()->getId()));
        }

        if ($file && file_exists($file)) {
            $contents = $this->base64Encode($this->getFileContents($file));
        } else {
            $contents = '';
        }

        list(, $sessionId) = array_values($this->call('StartSession', array('', $contents, true, '')));

        $this->session = new Session($sessionId);
    }

    /**
     * @return boolean whether the session has been opened
     */
    public function isSessionOpened()
    {
        return $this->session ? true : false;
    }

    /**
     * Creates a new DigiDoc container.
     *
     * @throws ApiException If the response status is incorrect
     */
    public function createContainer()
    {
        $this->call('createSignedDoc', array($this->getSession()->getId(), self::DOC_FORMAT, self::DOC_VERSION));
    }

    /**
     * Adds a new file to the given session
     *
     * @param File $file
     */
    public function addFile(File $file)
    {
        $this->call('addDataFile', $foo = array(
            $this->getSession()->getId(),
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
     * @param Certificate $certificate
     *
     * @return Signature
     */
    public function createSignature(Certificate $certificate)
    {
        list(, $signatureId, $challenge) = array_values($this->call('prepareSignature', array(
            $this->getSession()->getId(),
            $certificate->getCertificate(),
            $certificate->getId()
        )));

        return new Signature($this, $certificate, $signatureId, $challenge);
    }

    /**
     * Finalizes the signature, effectively making it valid.
     *
     * @todo throw ApiException if the solution is too short BEFORE making the call.
     *
     * @param Signature $signature
     * @param string    $solution
     *
     * @return boolean Whether the signature was successfully finished
     */
    public function finishSignature(Signature $signature, $solution)
    {
        if (self::SOLUTION_LENGTH !== strlen($solution)) {
            throw new ApiException(sprintf('Solution length must be "%d", got "%d".', self::SOLUTION_LENGTH, strlen($solution)));
        }

        list(, $info) = array_values($this->call('finalizeSignature', array(
            $this->getSession()->getId(),
            $signature->getId(),
            $solution,
        )));

        if ($this->isSignatureValid($signature, $info->SignatureInfo)) {
            return true;
        }

        // Removes the added invalid signature to preserve atomicity.
        $this->removeSignature($signature);

        return false;
    }

    /**
     * Removes the given signature.
     *
     * @param Signature $signature
     */
    public function removeSignature(Signature $signature)
    {
        $this->call('removeSignature', array($this->getSession(), $signature->getId()));
    }

    /**
     * Retrieves the contents of the opened file from the server.
     *
     * @return string
     */
    public function getContents()
    {
        list(, $contents) = array_values($this->call('getSignedDoc', array($this->getSession()->getId())));

        return $this->base64Decode($contents);
    }

    /**
     * Closes the given session with the DigiDoc service.
     */
    public function closeSession()
    {
        $this->call('closeSession', array($this->getSession()->getId()));
    }

    /**
     * Makes the actual call to the client and does initial status check.
     *
     * @param string $method
     * @param array  $arguments
     *
     * @return array
     *
     * @throws ApiException If the status is not "OK"
     */
    protected function call($method, array $arguments)
    {
        $result = $this->client->__soapCall(ucfirst($method), $arguments);

        if ('OK' !== $result['Status']) {
            throw ApiException::createIncorrectStatus($result['Status']);
        }

        return $result;
    }

    /**
     * @return Session
     *
     * @throws ApiException If the session is not opened
     */
    protected function getSession()
    {
        if (!$this->isSessionOpened()) {
            throw new ApiException('You must open a session before making any subsequent requests.');
        }

        return $this->session;
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
