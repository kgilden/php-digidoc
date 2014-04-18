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

class SignatureModulesArray
{
    /**
     *
     * @var SignatureModule $Modules
     * @access public
     */
    public $Modules = null;

    /**
     *
     * @param SignatureModule $Modules
     * @access public
     */
    public function __construct($Modules)
    {
        $this->Modules = $Modules;
    }
}
