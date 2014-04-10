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

/**
 * The central point through which all the communication with the DigiDoc
 * service flows.
 */
class Api
{
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
     * @param string $content
     *
     * @return Session
     */
    public function openSession($content = '')
    {
        list($status, $sessionId) = array_values(
            $this->client->__soapCall('StartSession', array('', $content, true, ''))
        );

        return new Session($sessionId);
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

        return $this->decodeBase64($contents);
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
     * @param Signature $signature
     * @param array     $signatures
     *
     * @return boolean
     */
    private function isSignatureValid(Signature $signature, array $signatures)
    {
        foreach ($signatures as $addedSignature) {
            if ($addedSignature->Id === $signature->getId()) {
                return 'OK' === $addedSignature->Status;
            }
        }

        return false;
    }

    /**
     * Decodes a piece of data from base64. The encoded data may be either
     * a long string in base64 or delimited by newline characters.
     *
     * @param string $encoded
     *
     * @return string
     */
    private function decodeBase64($encoded)
    {
        $decoded    = '';
        $delimiters = "\n";
        $token      = strtok($encoded, $delimiters);

        while (false !== $token) {
            $decoded .= base64_decode($token);

            $token = strtok($delimiters);
        }

        return $decoded;
    }
}
