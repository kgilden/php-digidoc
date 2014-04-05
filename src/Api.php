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
     * Closes the given session with the DigiDoc service.
     *
     * @param Session $session
     */
    public function closeSession(Session $session)
    {
        $this->client->__soapCall('CloseSession', array($session->getId()));
    }
}
