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

use KG\DigiDoc\Exception\UnexpectedTypeException;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class Container
{
    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $files;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $signatures;

    /**
     * @var Session
     */
    private $session;

    /**
     * @param Session     $session
     * @param File[]      $files
     * @param Signature[] $signatures
     */
    public function __construct(Session $session, $files = array(), $signatures = array())
    {
        $this->session = $session;
        $this->files = $files instanceof Collection ? $files : new ArrayCollection($files);
        $this->signatures = $signatures instanceof Collection ? $signatures : new ArrayCollection($signatures);
    }

    /**
     * @return Session
     */
    public function getSession()
    {
        return $this->session;
    }

    /**
     * Adds a new file to the container. NB! Files cannot be added once the
     * container has at least 1 signature.
     *
     * @param string|File $pathOrFile
     */
    public function addFile($pathOrFile)
    {
        $file = is_string($pathOrFile) ? new File($pathOrFile) : $pathOrFile;

        if (!($file instanceof File)) {
            throw new UnexpectedTypeException('string" or "\KG\DigiDoc\File', $file);
        }

        $this->getFiles()->add($file);
    }

    /**
     * @return Collection
     */
    public function getFiles()
    {
        return $this->files;
    }

    /**
     * Adds a new signature to the archive.
     *
     * @param Signature $signature
     */
    public function addSignature(Signature $signature)
    {
        $this->getSignatures()->add($signature);
    }

    /**
     * @return Collection
     */
    public function getSignatures()
    {
        return $this->signatures;
    }

    /**
     * Gets a signature given it's id in the container.
     *
     * @param string $signatureId The signature id (e.g. "S01")
     *
     * @return Signature|null The found signature or null, if no match was found
     */
    public function getSignature($signatureId)
    {
        foreach ($this->getSignatures() as $signature) {
            if ($signature->getId() === $signatureId) {
                return $signature;
            }
        }
    }
}
