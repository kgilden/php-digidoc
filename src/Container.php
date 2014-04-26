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
    public function __construct(Session $session, $files = [], $signatures = [])
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
     * @return Collection
     */
    public function getFiles()
    {
        return $this->files;
    }

    /**
     * @return Collection
     */
    public function getSignatures()
    {
        return $this->signatures;
    }
}
