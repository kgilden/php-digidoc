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

use Doctrine\Common\Collections\Collection;
use KG\DigiDoc\Exception\UnexpectedTypeException;

class Tracker
{
    protected $objects = [];

    public function add($objects)
    {
        $objects = is_array($objects) ? $objects : [$objects];

        foreach ($objects as $object) {
            if ($this->isTracked($object)) {
                continue;
            }

            $this->objects[] = $object;
        }
    }

    public function has($object)
    {
        return in_array($object, $this->objects);
    }
}
