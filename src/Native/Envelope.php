<?php

/*
 * This file is part of the DigiDoc package.
 *
 * (c) Kristen Gilden <kristen.gilden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KG\DigiDoc\Native;

use KG\DigiDoc\EnvelopeInterface;
use DOMDocument;

class Envelope implements EnvelopeInterface
{
    /**
     * @var \ZipArchive
     */
    private $archive;

    /**
     * @var string
     */
    private $path;

    /**
     * @param string $path
     */
    public function __construct($path)
    {
        $this->path = $path;

        $this->archive = new \ZipArchive();
        if (true !== ($error = $this->archive->open($this->path))) {
            // @todo better exception?
            throw new \RuntimeException(sprintf('Failed to open archive "%s", ZipArchive code %d', $this->path, $error));
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getFiles()
    {
        $nonMetaNames = array();

        foreach ($this->getAllFileNames() as $fileName) {
            // Skip metadata files.
            if (0 === strpos($fileName, 'META-INF/') || 'mimetype' === $fileName) {
                continue;
            }

            $nonMetaNames[] = $fileName;
        }

        return new \ArrayIterator($this->convertNamesToFullPaths($nonMetaNames));
    }

    /**
     * {@inheritDoc}
     */
    public function getSignatures()
    {
        $signatures = array();

        foreach ($this->getAllFileNames() as $fileName) {
            if (preg_match('/^META-INF\/signatures\d+\.xml$/', $fileName)) {
                $signatures[] = $this->createSignature(
                    $this->convertNameToFullPath($fileName)
                );
            }
        }

        return new \ArrayIterator($signatures);
    }

    public function __destruct()
    {
        $this->archive->close();
    }

    /**
     * @param array $names
     *
     * @return array
     */
    private function convertNamesToFullPaths($names)
    {
        $paths = array();

        foreach ($names as $name) {
            $paths[] = $this->convertNameToFullPath($name);
        }

        return $paths;
    }

    /**
     * @param string $name
     *
     * @return string
     */
    private function convertNameToFullPath($name)
    {
        return sprintf('zip://%s#%s', $this->path, $name);
    }

    /**
     * @return array
     */
    private function getAllFileNames()
    {
        $fileNames = array();

        for ($i = 0; $i < $this->archive->numFiles; $i++) {
            $fileNames[] = $this->archive->getNameIndex($i);
        }

        return $fileNames;
    }

    /**
     * Creates a new Signature from the given file.
     *
     * @param string $path
     *
     * @return Signature A new Signature object
     *
     * @throws \RuntimeException If the path is not readable
     */
    private function createSignature($path)
    {
        if (!$signatureContents = @file_get_contents($path)) {
            throw new \RuntimeException(sprintf('Failed to open signature "%s" for reading.', $path));
        }

        return new Signature(new DOMDocument($signatureContents));
    }
}
