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

class SignerRole
{
    /**
     *
     * @var int $Certified
     * @access public
     */
    public $Certified = null;

    /**
     *
     * @var string $Role
     * @access public
     */
    public $Role = null;

    /**
     *
     * @param int $Certified
     * @param string $Role
     * @access public
     */
    public function __construct($Certified, $Role)
    {
        $this->Certified = $Certified;
        $this->Role = $Role;
    }
}
