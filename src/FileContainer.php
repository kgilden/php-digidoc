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
        if (!$this->session) {
            $this->session = $this->api->openSession($this->toBase64());
        }

        return $this->session;
    }

    /**
     * Encodes the container to base 64.
     *
     * @return string
     */
    private function toBase64()
    {
        $level = error_reporting(0);
        $content = file_get_contents($this->container->getPathname());
        error_reporting($level);

        if (false === $content) {
            $error = error_get_last();
            throw new RuntimeException($error['message']);
        }

        return base64_encode($content);
    }
}
