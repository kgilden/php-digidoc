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

class DataFileInfo
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
     * @var int $Size
     * @access public
     */
    public $Size = null;

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
     * @var DataFileAttribute $Attributes
     * @access public
     */
    public $Attributes = null;

    /**
     *
     * @param string $Id
     * @param string $Filename
     * @param string $MimeType
     * @param string $ContentType
     * @param int $Size
     * @param string $DigestType
     * @param string $DigestValue
     * @param DataFileAttribute $Attributes
     * @access public
     */
    public function __construct($Id, $Filename, $MimeType, $ContentType, $Size, $DigestType, $DigestValue, $Attributes)
    {
        $this->Id = $Id;
        $this->Filename = $Filename;
        $this->MimeType = $MimeType;
        $this->ContentType = $ContentType;
        $this->Size = $Size;
        $this->DigestType = $DigestType;
        $this->DigestValue = $DigestValue;
        $this->Attributes = $Attributes;
    }
}
