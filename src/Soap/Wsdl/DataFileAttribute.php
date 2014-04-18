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

class DataFileAttribute
{
    /**
     *
     * @var string $Name
     * @access public
     */
    public $Name = null;

    /**
     *
     * @var string $Value
     * @access public
     */
    public $Value = null;

    /**
     *
     * @param string $Name
     * @param string $Value
     * @access public
     */
    public function __construct($Name, $Value)
    {
        $this->Name = $Name;
        $this->Value = $Value;
    }
}
