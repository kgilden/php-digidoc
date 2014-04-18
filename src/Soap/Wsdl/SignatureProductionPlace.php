<?php

/*
 * This file is part of the DigiDoc package.
 *
 * (c) Kristen Gilden <kristen.gilden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KG\DiciDoc\Soap\Wsdl;

class SignatureProductionPlace
{
    /**
     *
     * @var string $City
     * @access public
     */
    public $City = null;

    /**
     *
     * @var string $StateOrProvince
     * @access public
     */
    public $StateOrProvince = null;

    /**
     *
     * @var string $PostalCode
     * @access public
     */
    public $PostalCode = null;

    /**
     *
     * @var string $CountryName
     * @access public
     */
    public $CountryName = null;

    /**
     *
     * @param string $City
     * @param string $StateOrProvince
     * @param string $PostalCode
     * @param string $CountryName
     * @access public
     */
    public function __construct($City, $StateOrProvince, $PostalCode, $CountryName)
    {
        $this->City = $City;
        $this->StateOrProvince = $StateOrProvince;
        $this->PostalCode = $PostalCode;
        $this->CountryName = $CountryName;
    }
}
