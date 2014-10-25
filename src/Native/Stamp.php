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
    private $view;

    private $signer;

    public function __construct(BDocView $view, Signer $signer)
    {
        $this->view = $view;
        $this->signer = $signer;
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
     * @param string $signature raw binary signature
     */
    public function sign($signature)
    {
        // @todo verify before happily adding it?
        $this->view->addSignature(base64_encode($signature));

        // @todo make OCSP request
        $bytesToSend;
    }
}
