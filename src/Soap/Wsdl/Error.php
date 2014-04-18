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

class Error
{
    /**
     *
     * @var int $Code
     * @access public
     */
    public $Code = null;

    /**
     *
     * @var string $Category
     * @access public
     */
    public $Category = null;

    /**
     *
     * @var string $Description
     * @access public
     */
    public $Description = null;

    /**
     *
     * @param int $Code
     * @param string $Category
     * @param string $Description
     * @access public
     */
    public function __construct($Code, $Category, $Description)
    {
        $this->Code = $Code;
        $this->Category = $Category;
        $this->Description = $Description;
    }
}
