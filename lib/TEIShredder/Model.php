<?php

/**
 * Copyright (c) 2012, Carsten Blüm <carsten@bluem.net>
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * Redistributions of source code must retain the above copyright notice, this
 * list of conditions and the following disclaimer.
 * Redistributions in binary form must reproduce the above copyright notice, this
 * list of conditions and the following disclaimer in the documentation and/or
 * other materials provided with the distribution.
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE
 * FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
 * DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
 * OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

namespace TEIShredder;

use \UnexpectedValueException;

/**
 * Simple base class for TEIShredder model classes.
 *
 * @package   TEIShredder
 * @author    Carsten Bluem <carsten@bluem.net>
 * @copyright 2012 Carsten Bluem <carsten@bluem.net>
 * @link      https://github.com/BlueM/TEIShredder
 * @license   http://www.opensource.org/licenses/bsd-license.php BSD License
 */
abstract class Model
{

    /**
     * Returns data to be passed to a persistence layer.
     *
     * Concrete subclasses can use this method to check the consistency
     * of the objects state and throw an exception, if validation failed.
     *
     * @return array Associative array of property=>value pairs
     */
    abstract public function persistableData();

    /**
     * Returns one of the class properties' values
     *
     * @param $name
     *
     * @return mixed
     * @throws UnexpectedValueException
     */
    public function __get($name)
    {
        if ('_' === substr($name, 0, 1) or
            !in_array($name, array_keys(get_object_vars($this)))
        ) {
            throw new UnexpectedValueException("Invalid property “".$name."”");
        }
        return $this->$name;
    }

    /**
     * Magic method for setting protected object properties from outside.
     *
     * @param string $name  Property name
     * @param mixed  $value Value to be assigned
     *
     * @throws UnexpectedValueException
     */
    public function __set($name, $value)
    {
        if ('_' === substr($name, 0, 1) or
            !in_array($name, array_keys(get_object_vars($this)))
        ) {
            throw new UnexpectedValueException("Invalid property “".$name."”");
        }
        $this->$name = $value;
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function __toString()
    {
        $properties = array();
        foreach ($this->toArray() as $property => $value) {
            $properties[] = $property.': '.$value;
        }
        $properties = join(', ', $properties);
        return get_class($this).($properties ? " [$properties]" : '');
    }

    /**
     * Returns an array representation of the object.
     *
     * @return array
     */
    public function toArray()
    {
        $array = array();
        foreach ($this as $property => $value) {
            if (strncmp('_', $property, 1)) {
                $array[$property] = $value;
            }
        }
        return $array;
    }

}
