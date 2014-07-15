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
     * Creates a new DigiDoc container.
     *
     * @api
     *
     * @return Container
     */
    public function create();

    /**
     * Creates a new DigiDoc container from its string contents.
     *
     * @api
     *
     * @param string $bytes  DigiDoc container raw bytes
     *
     * @return Container
     */
    public function fromString($bytes);

    /**
     * Opens a DigiDoc container given the path to it.
     *
     * @todo Perhaps it's possible to create a stream of some sort?
     *
     * @api
     *
     * @param string $path Path to the DigiDoc container
     *
     * @return Container
     */
    public function open($path);

    /**
     * Closes the session between the local and remote systems of the given
     * DigiDoc container. This must be the last method called after all other
     * transactions.
     *
     * @todo Think of solutions where this would not be necessary
     *
     * @api
     *
     * @param Container $container
     */
    public function close(Container $container);

    /**
     * Updates the state in the remote api to match the contents of the given
     * DigiDoc container. The following is done in the same order:
     *
     *  - new files uploaded;
     *  - new signatures added and challenges injected;
     *  - signatures with solutions to challenges sealed;
     *
     * @api
     *
     * @param Container $container
     */
    public function update(Container $container);

    /**
     * Downloads the contents of the DigiDoc container from the server and
     * outputs it.
     *
     * @todo Perhaps it's possible to create a stream of some sort?
     *
     * @api
     *
     * @param Container $container
     *
     * @return string
     */
    public function toString(Container $container);

    /**
     * Downloads the contents of the DigiDoc container from the server and
     * writes them to the given local path. If you modify a container and call
     * this method without prior updating, the changes will not be reflected
     * in the written file.
     *
     * @api
     *
     * @param Container $container
     * @param string    $path
     */
    public function write(Container $container, $path);

    /**
     * Merges the DigiDoc container back with the api. This is necessary, when
     * working with a container over multiple requests and storing it somewhere
     * (session, database etc) inbetween the requests.
     *
     * @param Container $container
     */
    public function merge(Container $container);
}
