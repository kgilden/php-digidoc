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

    public function track($object)
    {
        if ($this->isTracked($object)) {
            return;
        }

        $this->objects[] = $object;
    }

    public function trackMultiple(array $objects)
    {
        foreach ($objects as $object) {
            $this->track($object);
        }
    }

    public function filterUntracked($objects)
    {
        $tracker = $this;

        $filterFn = function ($object) use ($tracker) {
            return !$tracker->isTracked($object);
        };

        if ($objects instanceof Collection) {
            return $objects->filter($filterFn);
        }

        if (is_array($objects)) {
            return array_filter($objects, $filterFn);
        }

        throw new UnexpectedTypeException('Doctrine\Common\Collections\Collection" or "array', $objects);
    }

    public function isTracked($object)
    {
        return in_array($object, $this->objects);
    }
}
