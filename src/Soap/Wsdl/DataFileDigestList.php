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

class DataFileDigestList
{
    /**
     *
     * @var DataFileDigest $DataFileDigest
     * @access public
     */
    public $DataFileDigest = null;

    /**
     *
     * @param DataFileDigest $DataFileDigest
     * @access public
     */
    public function __construct($DataFileDigest)
    {
        $this->DataFileDigest = $DataFileDigest;
    }
}
