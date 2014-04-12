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
     * @var boolean
     */
    private $isNewFile;

    /**
     * @var Session
     */
    private $session;

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
        $this->isNewFile = !file_exists($file);
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
     * @return array
     */
    public function serialize()
    {
        return serialize(array(
            'api' => $this->api,
            'container' => $this->container->getPathname(),
            'session' => $this->session,
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
        $this->isNewFile = !file_exists($this->container);
        $this->session = $serialized['session'];
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

            $file = $this->isNewFile() ? null : $this->container;

            $this->session = $this->api->openSession($file);

            if ($this->isNewFile()) {
                $this->api->createContainer($this->getSession());
            }
        }

        return $this->session;
    }

    /**
     * @return boolean
     */
    protected function isNewFile()
    {
        return $this->isNewFile;
    }

    /**
     * @return boolean
     */
    private function isSessionStarted()
    {
        return $this->session;
    }
}
