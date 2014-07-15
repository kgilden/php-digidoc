<?php

/*
 * This file is part of the DigiDoc package.
 *
 * (c) Kristen Gilden <kristen.gilden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KG\DigiDoc;

use KG\DigiDoc\Soap\Wsdl\DataFileInfo;
use Symfony\Component\HttpFoundation\File\File as SfFile;

class File
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $mimeType;

    /**
     * @var integer
     */
    private $size;

    /**
     * @var string
     */
    private $pathname;

    /**
     * @param string|null $pathname
     */
    public function __construct($pathname = null)
    {
        $this->pathname = $pathname;

        if ($pathname) {
            $file = new SfFile($pathname);

            $this->name = $file->getBasename();
            $this->id = $this->name;
            $this->mimeType = $file->getMimeType();
            $this->size = $file->getSize();
        }
    }

    /**
     * @param DataFileInfo $info
     *
     * @return File
     */
    public static function createFromSoap(DataFileInfo $info)
    {
        $file = new static();
        $file->id       = $info->Id;
        $file->name     = $info->Filename;
        $file->mimeType = $info->MimeType;
        $file->size     = $info->Size;

        return $file;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getMimeType()
    {
        return $this->mimeType;
    }

    /**
     * @return integer
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * @todo A duplicate of Encoder::getFileContent()
     *
     * @return string
     *
     * @throws \LogicException If the file is not real
     */
    public function getContent()
    {
        if (!($pathname = $this->getPathname())) {
            throw new \LogicException('No pathname was specified - maybe it came from DigiDoc web service?');
        }

        $level = error_reporting(0);
        $content = file_get_contents($pathname);
        error_reporting($level);

        if (false === $content) {
            $error = error_get_last();
            throw new RuntimeException($error['message']);
        }

        return $content;
    }

    /**
     * @return string|null
     */
    public function getPathname()
    {
        return $this->pathname;
    }
}
