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

class Location
{
    /**
     * @var string
     */
    private $city;

    /**
     * @var string
     */
    private $stateOrProvince;

    /**
     * @var string
     */
    private $postalCode;

    /**
     * @var string
     */
    private $country;

    /**
     * @param string $city
     */
    public function setCity($city)
    {
        $this->city = $city;
    }

    /**
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @param string $stateOrProvince
     */
    public function setStateOrProvince($stateOrProvince)
    {
        $this->stateOrProvince;
    }

    /**
     * @return string|null
     */
    public function getStateOrProvince()
    {
        return $this->stateOrProvince;
    }

    /**
     * @param string $postalCode
     */
    public function setPostalCode($postalCode)
    {
        $this->postalCode = $postalCode;
    }

    /**
     * @return string|null
     */
    public function getPostalCode()
    {
        return $this->postalCode;
    }

    /**
     * @param string $country
     */
    public function setCountry($country)
    {
        $this->country = $country;
    }

    /**
     * @return string|null
     */
    public function getCountry()
    {
        return $this->country;
    }
}
