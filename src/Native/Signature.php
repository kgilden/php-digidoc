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

class Signature
{
    /**
     * @var Envelope
     */
    private $envelope;

    /**
     * @var Certificate
     */
    private $certificate;

    /**
     * @param Envelope    $envelope    Envelope containing the signature
     * @param Certificate $certificate The certificate used for signing
     */
    public function __construct(Envelope $envelope, Certificate $certificate)
    {
        $this->envelope = $envelope;
        $this->certificate = $certificate;
    }
}
