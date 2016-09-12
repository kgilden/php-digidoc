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

interface ApiInterface
{
    /**
     * Creates a new DigiDoc envelope.
     *
     * @api
     *
     * @return Envelope
     */
    public function create();

    /**
     * Creates a new DigiDoc envelope from its string contents.
     *
     * @api
     *
     * @param string $bytes  DigiDoc envelope raw bytes
     *
     * @return Envelope
     */
    public function fromString($bytes);

    /**
     * Opens a DigiDoc envelope given the path to it.
     *
     * @todo Perhaps it's possible to create a stream of some sort?
     *
     * @api
     *
     * @param string $path Path to the DigiDoc envelope
     *
     * @return Envelope
     */
    public function open($path);

    /**
     * Closes the session between the local and remote systems of the given
     * DigiDoc envelope. This must be the last method called after all other
     * transactions.
     *
     * @todo Think of solutions where this would not be necessary
     *
     * @api
     *
     * @param Envelope $envelope
     */
    public function close(Envelope $envelope);

    /**
     * Updates the state in the remote api to match the contents of the given
     * DigiDoc envelope. The following is done in the same order:
     *
     *  - new files uploaded;
     *  - new signatures added and challenges injected;
     *  - signatures with solutions to challenges sealed;
     *
     * @api
     *
     * @param Envelope $envelope
     */
    public function update(Envelope $envelope);

    /**
     * Downloads the contents of the DigiDoc envelope from the server and
     * outputs it.
     *
     * @todo Perhaps it's possible to create a stream of some sort?
     *
     * @api
     *
     * @param Envelope $envelope
     *
     * @return string
     */
    public function toString(Envelope $envelope);

    /**
     * Downloads the contents of the DigiDoc envelope from the server and
     * writes them to the given local path. If you modify a envelope and call
     * this method without prior updating, the changes will not be reflected
     * in the written file.
     *
     * @api
     *
     * @param Envelope $envelope
     * @param string   $path
     */
    public function write(Envelope $envelope, $path);

    /**
     * Merges the DigiDoc envelope back with the api. This is necessary, when
     * working with a envelope over multiple requests and storing it somewhere
     * (session, database etc) inbetween the requests.
     *
     * @param Envelope $envelope
     */
    public function merge(Envelope $envelope);

    /**
     * Sets the Client.
     * Api can not be stored in a session without SoapClient failing. So in
     * order for storing the Api along with other objects in session, we
     * need to replace the client after retrieving the objects.
     *
     * @param \SoapClient $client
     */
    public function setClient(\SoapClient $client);
}
