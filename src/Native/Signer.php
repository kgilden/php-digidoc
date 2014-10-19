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

class Signer
{
    /**
     * @var Cert
     */
    private $cert;

    /**
     * @var Location|null
     */
    private $location;

    /**
     * @var string|null
     */
    private $role;

    /**
     * @param Cert          $cert
     * @param Location|null $location
     * @param string|null   $role
     */
    public function __construct(Cert $cert, Location $location = null, $role = null)
    {
        $this->cert = $cert;
        $this->location = $location;
        $this->role = $role;
    }

    /**
     * @return Cert
     */
    public function getCert()
    {
        return $this->cert;
    }

    /**
     * @return Location
     */
    public function getLocation()
    {
        return $this->location ?: $this->location = new Location();
    }

    /**
     * @return string|null
     */
    public function getRole()
    {
        return $this->role;
    }
}
