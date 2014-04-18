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

class RevokedInfo
{
    /**
     *
     * @var int $Sequence
     * @access public
     */
    public $Sequence = null;

    /**
     *
     * @var string $SerialNumber
     * @access public
     */
    public $SerialNumber = null;

    /**
     *
     * @var dateTime $RevocationDate
     * @access public
     */
    public $RevocationDate = null;

    /**
     *
     * @param int $Sequence
     * @param string $SerialNumber
     * @param dateTime $RevocationDate
     * @access public
     */
    public function __construct($Sequence, $SerialNumber, $RevocationDate)
    {
        $this->Sequence = $Sequence;
        $this->SerialNumber = $SerialNumber;
        $this->RevocationDate = $RevocationDate;
    }
}
