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
 * Represents a certificate.
 */
class Certificate
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $certificate;

    /**
     * @todo clarify the format of a certificate (e.g. an example)
     *
     * @param string $id          Referred to as "token id" in DigiDoc manual
     * @param string $certificate The certificate in hex
     */
    public function __construct($id, $certificate)
    {
        $this->id = $id;
        $this->certificate;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getCertificate()
    {
        return $this->certificate;
    }
}
