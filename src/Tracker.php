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
    protected $objects = array();

    /**
     * Adds the given objects to the tracker. You can pass either a single
     * object or an array of objects.
     *
     * @param object|object[] $objects
     */
    public function add($objects)
    {
        $objects = is_array($objects) ? $objects : array($objects);

        foreach ($objects as $object) {
            if ($this->has($object)) {
                continue;
            }

            $this->objects[] = $object;
        }
    }

    /**
     * @param object $object
     *
     * @return boolean Whether the tracker is tracking this object
     */
    public function has($object)
    {
        return in_array($object, $this->objects);
    }
}
