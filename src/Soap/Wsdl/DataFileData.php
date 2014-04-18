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

class DataFileData
{
    /**
     *
     * @var string $Id
     * @access public
     */
    public $Id = null;

    /**
     *
     * @var string $Filename
     * @access public
     */
    public $Filename = null;

    /**
     *
     * @var string $MimeType
     * @access public
     */
    public $MimeType = null;

    /**
     *
     * @var string $ContentType
     * @access public
     */
    public $ContentType = null;

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
     * @var int $Size
     * @access public
     */
    public $Size = null;

    /**
     *
     * @var DataFileAttribute $Attributes
     * @access public
     */
    public $Attributes = null;

    /**
     *
     * @var string $DfData
     * @access public
     */
    public $DfData = null;

    /**
     *
     * @param string $Id
     * @param string $Filename
     * @param string $MimeType
     * @param string $ContentType
     * @param string $DigestType
     * @param string $DigestValue
     * @param int $Size
     * @param DataFileAttribute $Attributes
     * @param string $DfData
     * @access public
     */
    public function __construct($Id, $Filename, $MimeType, $ContentType, $DigestType, $DigestValue, $Size, $Attributes, $DfData)
    {
        $this->Id = $Id;
        $this->Filename = $Filename;
        $this->MimeType = $MimeType;
        $this->ContentType = $ContentType;
        $this->DigestType = $DigestType;
        $this->DigestValue = $DigestValue;
        $this->Size = $Size;
        $this->Attributes = $Attributes;
        $this->DfData = $DfData;
    }
}
