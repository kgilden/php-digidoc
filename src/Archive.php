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
use KG\DigiDoc\Collections\FileCollection;
use KG\DigiDoc\Collections\SignatureCollection;

class Archive
{
    /**
     * @var ArrayCollection
     */
    private $files;

    /**
     * @var ArrayCollection
     */
    private $signatures;

    /**
     * @var Session
     */
    private $session;

    /**
     * @param Session                  $session
     * @param FileCollection|null      $files
     * @param SignatureCollection|null $signatures
     */
    public function __construct(Session $session, FileCollection $files = null, SignatureCollection $signatures = null)
    {
        $this->session = $session;
        $this->files = $files ?: new FileCollection();
        $this->signatures = $signatures ?: new SignatureCollection();
    }

    /**
     * @return Session
     */
    public function getSession()
    {
        return $this->session;
    }

    /**
     * @return FileCollection
     */
    public function getFiles()
    {
        return $this->files;
    }

    /**
     * @return SignatureCollection
     */
    public function getSignatures()
    {
        return $this->signatures;
    }
}
