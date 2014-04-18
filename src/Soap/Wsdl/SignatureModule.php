<?php

/*
 * This file is part of the DigiDoc package.
 *
 * (c) Kristen Gilden <kristen.gilden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KG\DigiDoc\Soap\Wsdl;

class SignatureModule
{
    /**
     *
     * @var string $Name
     * @access public
     */
    public $Name = null;

    /**
     *
     * @var string $Type
     * @access public
     */
    public $Type = null;

    /**
     *
     * @var string $Location
     * @access public
     */
    public $Location = null;

    /**
     *
     * @var string $ContentType
     * @access public
     */
    public $ContentType = null;

    /**
     *
     * @var string $Content
     * @access public
     */
    public $Content = null;

    /**
     *
     * @param string $Name
     * @param string $Type
     * @param string $Location
     * @param string $ContentType
     * @param string $Content
     * @access public
     */
    public function __construct($Name, $Type, $Location, $ContentType, $Content)
    {
        $this->Name = $Name;
        $this->Type = $Type;
        $this->Location = $Location;
        $this->ContentType = $ContentType;
        $this->Content = $Content;
    }
}
