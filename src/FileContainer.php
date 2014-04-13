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
class FileContainer implements \Serializable
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
     * @param Api         $api       The api for modifying this container
     * @param File|string $file      The file or its path
     * @param boolean     $checkPath Whether to check the path or not
     */
    public function __construct(Api $api, $file, $checkPath = true)
    {
        if (is_string($file)) {
            $file = new File($file, $checkPath);
        }

        if (!$file instanceof File) {
            throw new UnexpectedTypeException('\Symfony\Component\HttpFoundation\File\File" or "string', $file);
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
        return $this->getApi()->createSignature($certificate);
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

        return $this->getApi()->addFile($file);
    }

    /**
     * @return array
     */
    public function serialize()
    {
        return serialize(array(
            'api' => $this->api,
            'container' => $this->container->getPathname(),
        ));
    }

    /**
     * @param string $serialized
     */
    public function unserialize($serialized)
    {
        $serialized = unserialize($serialized);

        $this->api = $serialized['api'];
        $this->container = new File($serialized['container'], false);
    }

    /**
     * Writes the changes of the container to the disk.
     *
     * @return FileContainer
     */
    public function write()
    {
        if (!$this->api->isSessionOpened()) {
            return $this;
        }

        file_put_contents($this->__toString(), $this->getApi()->getContents());

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
     * @return Api
     */
    private function getApi()
    {
        if (!$this->api->isSessionOpened()) {
            $this->api->openSession($this->container);
        }

        return $this->api;
    }
}
