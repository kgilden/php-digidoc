<?php

/*
 * This file is part of the DigiDoc package.
 *
 * (c) Kristen Gilden <kristen.gilden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KG\DigiDoc\Native;

class Cert
{
    /**
     * @var resource
     */
    private $x509Cert;

    /**
     * @todo should openssl_error_string() be checked here? Doesn't look
     * like it, because malformed cert data triggers an error.
     *
     * @param mixed $x509CertData
     */
    public function __construct($x509CertData)
    {
        $this->x509Cert = openssl_x509_read($x509CertData);
    }

    /**
     * Creates a new certificate from a PEM-encoded certificate missing
     * "BEGIN CERTIFICATE" and "END CERTIFICATE" lines.
     *
     * @param string $pem
     *
     * @return Certificate
     */
    public static function fromPemWithoutWrappers($pem)
    {
        $pem = "-----BEGIN CERTIFICATE-----\n".$pem."-----END CERTIFICATE-----\n";

        return new static($pem);
    }

    public function __toString()
    {
        return print_r(openssl_x509_parse($this->x509Cert), true);
    }

    public function __destruct()
    {
        openssl_x509_free($this->x509Cert);
    }
}
