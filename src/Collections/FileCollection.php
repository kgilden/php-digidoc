<?php

/*
 * This file is part of the DigiDoc package.
 *
 * (c) Kristen Gilden <kristen.gilden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KG\DigiDoc\Collections;

use Doctrine\Common\Collections\ArrayCollection;

class FileCollection extends ArrayCollection
{
    /**
     * @param array $files
     */
    public function __construct($files = [])
    {
        parent::__construct(is_array($files) ? $files : [$files]);
    }
}
