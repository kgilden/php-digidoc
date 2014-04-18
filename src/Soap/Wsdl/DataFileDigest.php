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

class DataFileDigest
{
    /**
     *
     * @var string $Id
     * @access public
     */
    public $Id = null;

    /**
     *
     * @var string $DigestType
     * @access public
     */
    public $DigestType = null;

    /**
     *
     * @var string $DigestValue
     * @access public
     */
    public $DigestValue = null;

    /**
     *
     * @param string $Id
     * @param string $DigestType
     * @param string $DigestValue
     * @access public
     */
    public function __construct($Id, $DigestType, $DigestValue)
    {
        $this->Id = $Id;
        $this->DigestType = $DigestType;
        $this->DigestValue = $DigestValue;
    }
}
