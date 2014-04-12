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

use KG\DigiDoc\Exception\RuntimeException;
use KG\DigiDoc\Exception\UnexpectedTypeException;
use Symfony\Component\HttpFoundation\File\File;

/**
 * Representation of a DigiDoc file.
 */
class FileContainer
{
    /**
     * @var Api
     */
    private $api;

    /**
     * @var \SplFileInfo
     */
    private $container;

    /**
     * @var Session
     */
    private $session;

    /**
     * @param Api                 $api  The api for modifying this container
     * @param \SplFileInfo|string $file The file or its path
     */
    public function __construct(Api $api, $file)
    {
        if (is_string($file)) {
            $file = new \SplFileInfo($file);
        }

        if (!$file instanceof \SplFileInfo) {
            throw new UnexpectedTypeException('\SplFileInfo" or "string', $file);
        }

        $this->api = $api;
        $this->container = $file;
    }

    /**
     * Creates a new unsealed signature for this container. NB! You must "seal"
     * the signature to make it count.
     *
     * @todo Refer to Signature sealing
     *
     * @param Certificate $certificate
     *
     * @return Signature
     */
    public function createSignature(Certificate $certificate)
    {
        return $this->api->createSignature($this->getSession(), $certificate);
    }

    /**
     * Adds a new file to the container.
     *
     * @todo Prevent from adding a file, if the container has signatures
     *
     * @param string|File $file
     */
    public function addFile($file)
    {
        if (is_string($file)) {
            $file = new File($file);
        }

        if (!($file instanceof File)) {
            throw new UnexpectedTypeException('Symfony\Component\HttpFoundation\File\File', $file);
        }

        return $this->api->addFile($this->getSession(), $file);
    }

    /**
     * Writes the changes of the container to the disk.
     *
     * @return FileContainer
     */
    public function write()
    {
        if (!$this->isSessionStarted()) {
            return $this;
        }

        file_put_contents($this->__toString(), $this->api->getContents($this->getSession()));

        return $this;
    }

    /**
     * Makes it possible to use regular file operations (e.g. file_exists).
     *
     * @return string
     */
    public function __toString()
    {
        return $this->container->getPathname();
    }

    /**
     * @return Session
     */
    protected function getSession()
    {
        if (!$this->isSessionStarted()) {
            $this->session = $this->api->openSession($this->getContents());
        }

        return $this->session;
    }

    /**
     * @return boolean
     */
    private function isSessionStarted()
    {
        return $this->session;
    }

    /**
     * Gets the contents of the container.
     *
     * @return string
     */
    private function getContents()
    {
        $level = error_reporting(0);
        $contents = file_get_contents($this->container->getPathname());
        error_reporting($level);

        if (false === $contents) {
            $error = error_get_last();
            throw new RuntimeException($error['message']);
        }

        return $contents;
    }
}
