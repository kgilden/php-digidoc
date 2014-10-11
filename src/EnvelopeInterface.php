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

interface EnvelopeInterface
{
    /**
     * @return \Iterator
     */
    public function getFiles();

    /**
     * @return \Iterator
     */
    public function getSignatures();
}
