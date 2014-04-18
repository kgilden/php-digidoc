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

class CertificatePolicy
{
    /**
     *
     * @var string $OID
     * @access public
     */
    public $OID = null;

    /**
     *
     * @var string $URL
     * @access public
     */
    public $URL = null;

    /**
     *
     * @var string $Description
     * @access public
     */
    public $Description = null;

    /**
     *
     * @param string $OID
     * @param string $URL
     * @param string $Description
     * @access public
     */
    public function __construct($OID, $URL, $Description)
    {
        $this->OID = $OID;
        $this->URL = $URL;
        $this->Description = $Description;
    }
}
