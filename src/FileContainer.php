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
     * Gets a list of files in the current container. Metadata files are
     * ignored. The Api is used, if this container is not saved yet.
     *
     * @todo tests
     *
     * @return string[]
     */
    public function listFiles()
    {
        if (false !== ($archive = $this->openArchive($this->container))) {
            return $this->listArchiveFiles($archive);
        }

        throw new \Exception('Not implemented.');
    }

    /**
     * Extracts the archive contents. Metadata is omitted.
     *
     * @todo Perhaps this should not be supported - the user can use ZipArchive
     *       himself to do any extracting. However, FileContainer::listFiles
     *       seems to be more necessary and it uses ZipArchive anyways so might
     *       as well keep it.
     *
     * @todo tests
     *
     * @see http://www.php.net/manual/en/ziparchive.extractto.php
     *
     * @param string $destination Location where to extract the files
     * @param mixed  $entries     The entries to extract (accepts single name or an array of names)
     */
    public function extractTo($destination, $entries = array())
    {
        if (false === ($archive = $this->openArchive($this->container))) {
            throw new RuntimeException(sprintf('Cannot extract "%s", because it is not yet written on disk.', $this->container->getPathname()));
        }

        if (is_string($entries)) {
            $entries = array();
        }

        foreach ($this->listArchiveFiles($archive) as $entry) {
            if (!$entries || in_array($entry, $entries)) {
                $archive->extractTo($destination, $entry);
            }
        }
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

    /**
     * Lists the files in the given archive. We can tread BDOC files as Zip
     * archives. @todo provide a link as a reference
     *
     * @param \ZipArchive $file
     *
     * @return array
     */
    private function listArchiveFiles(\ZipArchive $archive) {
        $excludedPatterns = array(
            '^mimetype$',
            '^META-INF/'
        );

        $fileNames = array();

        for ($i = 0; $i < $archive->numFiles; $i++) {
            $fileName = $archive->getNameIndex($i);

            if (!$this->patternsMatch($excludedPatterns, $fileName)) {
                $fileNames[] = $fileName;
            }
        }

        return $fileNames;
    }

    /**
     * @param array  $patterns
     * @param string $value
     *
     * @return boolean Whether any of the given patterns match the value
     */
    private function patternsMatch(array $patterns, $value)
    {
        foreach ($patterns as $pattern) {
            if (preg_match('#'.$pattern.'#', $value)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param File $file
     *
     * @return \ZipArchive|boolean
     */
    private function openArchive(File $file)
    {
        if (!file_exists($file)) {
            return false;
        }

        $archive = new \ZipArchive();

        if (true !== ($result = $archive->open($file->getPathname()))) {
            $codes = array(
               10 => 'ZipArchive::ER_EXISTS',
               21 => 'ZipArchive::ER_INCONS',
               18 => 'ZipArchive::ER_INVAL',
               14 => 'ZipArchive::ER_MEMORY',
               9  => 'ZipArchive::ER_NOENT',
               19 => 'ZipArchive::ER_NOZIP',
               11 => 'ZipArchive::ER_OPEN',
               5  => 'ZipArchive::ER_READ',
               4  => 'ZipArchive::ER_SEEK',
            );

            throw new RuntimeException(sprintf(
                'Failed to open "%s" archive, error %s. See %s for details',
                $file->getPathname(),
                isset($codes[$result]) ? $codes[$result] : $result),
                'http://www.php.net/manual/en/ziparchive.open.php'
            );
        }

        return $archive;
    }
}
