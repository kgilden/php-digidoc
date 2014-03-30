<?php

/*
 * This file is part of the DigiDoc package.
 *
 * (c) Kristen Gilden <kristen.gilden@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KG\DigiDoc\Exception;

/**
 * Thrown when a value is of an unexpected type.
 */
class UnexpectedTypeException extends RuntimeException
{
    /**
     * @param string $expected Expected type name
     * @param mixed  $actual   The actual object
     */
    public function __construct($expected, $actual)
    {
        parent::__construct('Expected value to be of type "'.$expected.'", got "'.(is_object($actual) ? get_class($actual) : gettype($actual)).'".');
    }
}
