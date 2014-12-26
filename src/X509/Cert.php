<?php

/*
 * This file is part of the DigiDoc package.
 *
 * (c) Kristen Gilden <kristen.gilden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KG\DigiDoc\X509;

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

    /**
     * @param Signature $signature
     *
     * @return boolean Whether the signature has been signed by this certificate
     */
    public function hasSigned(Signature $signature)
    {
        $key = openssl_pkey_get_public($this->x509Cert);

        try {
            $hasSigned = $signature->isSignedByKey($key);
        } catch (\Excetpion $e) {
            openssl_pkey_free($key);
            throw $e;
        }

        openssl_pkey_free($key);

        return $hasSigned;
    }

    /**
     * @return string Issuer's Common Name
     */
    public function getIssuerCN()
    {
        $parsedCert = $this->getParsed();

        return $parsedCert['issuer']['CN'];
    }

    public function toParsed()
    {
        return print_r($this->getParsed(), true);
    }

    public function toPem()
    {
        // @todo this function may fail.
        openssl_x509_export($this->x509Cert, $x509CertData);

        return $x509CertData;
    }

    public function __toString()
    {
        return $this->pem2der($this->toPem());
    }

    public function __destruct()
    {
        openssl_x509_free($this->x509Cert);
    }

    /**
     * Source: http://php.net/manual/en/ref.openssl.php
     */
    private function pem2der($pem_data) {
        $begin = "CERTIFICATE-----";
        $end   = "-----END";
        $pem_data = substr($pem_data, strpos($pem_data, $begin) + strlen($begin));
        $pem_data = substr($pem_data, 0, strpos($pem_data, $end));

        return  base64_decode($pem_data);
    }

    private function getParsed()
    {
        return openssl_x509_parse($this->x509Cert);
    }
}
