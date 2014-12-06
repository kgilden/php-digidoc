<?php

/*
 * This file is part of the DigiDoc package.
 *
 * (c) Kristen Gilden <kristen.gilden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KG\DigiDoc\Ocsp;

use KG\DigiDoc\Exception\FileNotFoundException;

/**
 * Models a single OCSP request.
 */
class Request
{
    /**
     * @var string
     */
    private $pathToClientCert;

    /**
     * @var string
     */
    private $pathToIssuerCert;

    /**
     * @param string $pathToClientCert
     * @param string $pathToIssuerCert
     *
     * @throws FileNotFoundException If either of the paths are not files
     */
    public function __construct($pathToClientCert, $pathToIssuerCert)
    {
        if (!is_file($pathToClientCert)) {
            throw new FileNotFoundException($pathToClientCert);
        }

        if (!is_file($pathToIssuerCert)) {
            throw new FileNotFoundException($pathToIssuerCert);
        }

        $this->pathToClientCert = $pathToClientCert;
        $this->pathToIssuerCert = $pathToIssuerCert;
    }

    /**
     * @return string
     */
    public function getPathToClientCert()
    {
        return $this->pathToClientCert;
    }

    /**
     * @return string
     */
    public function getPathToIssuerCert()
    {
        return $this->pathToIssuerCert;
    }
}
