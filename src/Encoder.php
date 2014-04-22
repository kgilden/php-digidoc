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

class Encoder
{
    /**
     * Base64 encodes a string to the required format - split into 64 bytes
     * delimited by newline characters.
     *
     * @param string $data
     *
     * @return string
     */
    public function encode($data)
    {
        return chunk_split(base64_encode($data), 64, "\n");
    }

    /**
     * Same as Encoder::encode, but encodes the contents of a file.
     *
     * @param string $path
     *
     * @return string
     */
    public function encodeFileContent($path)
    {
        return $this->encode($this->getFileContent($path));
    }

    /**
     * Decodes a piece of data from base64. The encoded data may be either
     * a long string in base64 or delimited by newline characters.
     *
     * @param string $data The encoded data
     *
     * @return string
     */
    public function decode($data)
    {
        $decoded    = '';
        $delimiters = "\n";
        $token      = strtok($data, $delimiters);

        while (false !== $token) {
            $decoded .= base64_decode($token);

            $token = strtok($delimiters);
        }

        return $decoded;
    }

    /**
     * Gets the file content.
     *
     * @todo Refactor this out to some other class
     *
     * @param string $pathToFile
     *
     * @return string
     */
    private function getFileContent($pathToFile)
    {
        $level = error_reporting(0);
        $content = file_get_contents($pathToFile);
        error_reporting($level);

        if (false === $content) {
            $error = error_get_last();
            throw new RuntimeException($error['message']);
        }

        return $content;
    }
}
