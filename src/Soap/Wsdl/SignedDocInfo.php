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

class SignedDocInfo
{
    /**
     *
     * @var string $Format
     * @access public
     */
    public $Format = null;

    /**
     *
     * @var string $Version
     * @access public
     */
    public $Version = null;

    /**
     *
     * @var DataFileInfo $DataFileInfo
     * @access public
     */
    public $DataFileInfo = null;

    /**
     *
     * @var SignatureInfo $SignatureInfo
     * @access public
     */
    public $SignatureInfo = null;

    /**
     *
     * @param string $Format
     * @param string $Version
     * @param DataFileInfo $DataFileInfo
     * @param SignatureInfo $SignatureInfo
     * @access public
     */
    public function __construct($Format, $Version, $DataFileInfo, $SignatureInfo)
    {
        $this->Format = $Format;
        $this->Version = $Version;
        $this->DataFileInfo = $DataFileInfo;
        $this->SignatureInfo = $SignatureInfo;
    }
}
