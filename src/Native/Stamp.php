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

class Stamp
{
    /**
     * @var Signer
     */
    private $signer;

    /**
     * @var BDocView
     */
    private $view;

    /**
     * @param Signer   $signer
     * @param BDocView $view
     */
    public function __construct(Signer $signer, BDocView $view)
    {
        $this->signer = $signer;
        $this->view = $view;
    }

    /**
     * Gets the signer who should be giving the signature.
     *
     * @return Signer
     */
    public function getSigner()
    {
        return $this->signer;
    }

    public function getChallenge()
    {
        $dataToSign = $this->view->getDataToSign();

        // @todo get rid of hardcoded hash - grab from SignatureMethod?
        // @todo document that if the challenge will be signed via an in-browser
        //       plugin, it must first be hex-encoded and uppercased.
        return hash('sha256', $dataToSign, true);
    }

    /**
     * @param string $signature    raw binary signature
     * @param string $ocspResponse DER-encoded OCSP certificate response
     */
    public function sign($signature, $ocspResponse)
    {
        // @todo verify before happily adding it?
        $this->view->addSignature(base64_encode($signature));
        $this->view->addOcspResponse($ocspResponse);
    }
}
