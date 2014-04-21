<?php

/*
 * This file is part of the DigiDoc package.
 *
 * (c) Kristen Gilden <kristen.gilden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KG\DigiDoc\Collections;

use Doctrine\Common\Collections\ArrayCollection;
use KG\DigiDoc\Signature;

class SignatureCollection extends ArrayCollection
{
    /**
     * @param array $signatures
     */
    public function __construct($signatures = [])
    {
        parent::__construct(is_array($signatures) ? $signatures : [$signatures]);
    }

    /**
     * @return SignatureCollection
     */
    public function getUnsealed()
    {
        return $this->filter(function (Signature $signature) {
            return !$signature->isSealed();
        });
    }

    /**
     * @return SignatureCollection
     */
    public function getSolvable()
    {
        return $this->getUnsealed()->filter(function (Signature $signature) {
            return (boolean) $signature->getChallenge();
        });
    }

    /**
     * @return SignatureCollection
     */
    public function getSealable()
    {
        return $this->getUnsealed()->filter(function (Signature $signature) {
            return (boolean) $signature->getSolution();
        });
    }
}
