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
}
