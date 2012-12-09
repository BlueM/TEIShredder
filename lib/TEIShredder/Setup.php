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

use PDO;
use Closure;
use InvalidArgumentException;
use UnexpectedValueException;

/**
 * Configuration/dependency injection class.
 *
 * @package   TEIShredder
 * @author    Carsten Bluem <carsten@bluem.net>
 * @copyright 2012 Carsten Bluem <carsten@bluem.net>
 * @link      https://github.com/BlueM/TEIShredder
 * @license   http://www.opensource.org/licenses/bsd-license.php BSD License
 * @property string               $prefix
 * @property FactoryInterface     $factory
 * @property string|array|Closure $titleCallback
 * @property string|array|Closure $plaintextCallback
 * @property array                $chunktags
 * @property array                $nostacktags
 * @property array                $ignorabletags
 * @property array                $structureleveltags
 * @property array                $blocktags
 * @property PDO                  $database
 */
class Setup
{

    /**
     * Database table prefix.
     *
     * @var string
     */
    protected $prefix = '';

    /**
     * Array of element types / tag names that mark the beginning of
     * a new chunk of text (can be either empty or non-empty elements).
     *
     * @var array Indexed array of element names
     */
    protected $chunktags = array('pb', 'milestone', 'div', 'front', 'body', 'titlePage');

    /**
     * Array of element types / tag names that mark the beginning of
     * a new chunk of text, but which should not be indexed separately.
     * (Basically, these are more important for detecting text chunks'
     * ends than their beginning.)
     */
    protected $nostacktags = array('text', 'group');

    /**
     * Text that is inside these tags will skipped when extracting
     * plaintext fragments of the text.
     *
     * @var string[]
     */
    protected $ignorabletags = array('sic', 'del', 'orig');

    /**
     * Text that is inside these tags will be skipped when extracting
     * plaintext fragments of the text.
     *
     * @var string[]
     */
    protected $structureleveltags = array('div', 'titlePage');

    /**
     * Array of element types / tag names that are regarded as block
     * elements, i.e. after which whitespace is inserted.
     *
     * @var array Indexed array of element names
     */
    protected $blocktags = array(
        'p',
        'pb',
        'div',
        'milestone',
        'figure',
        'text',
        'body',
        'argument',
        'lb',
        'head'
    );

    /**
     * Factory instance
     *
     * @var FactoryInterface
     */
    protected $factory;

    /**
     * Database table prefix.
     *
     * @var PDO
     */
    protected $database;

    /**
     * Constructor.
     *
     * @param PDO                       $db
     * @param FactoryInterface|null     $factory  [optional]
     * @param string                    $prefix
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(PDO $db, FactoryInterface $factory = null, $prefix = '')
    {
        $this->database = $db;

        if ($factory) {
            $this->factory = $factory;
        } else {
            $this->factory = new DefaultFactory($db, $prefix);
        }

        $this->prefix = $prefix;
    }

    /**
     * Magic method for setting protected object properties.
     *
     * @param string $name  Property name
     * @param array  $value Value to be assigned
     *
     * @throws \UnexpectedValueException
     */
    public function __set($name, array $value)
    {
        switch ($name) {
            case 'chunktags':
            case 'nostacktags':
            case 'sectiontags':
            case 'ignorabletags':
            case 'structureleveltags':
            case 'blocktags':
                $this->$name = $value;
                break;
            default:
                throw new UnexpectedValueException("Invalid property name “".$name."”.");
        }
    }

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
        if (in_array($name, array_keys(get_class_vars(__CLASS__)))) {
            return $this->$name;
        }
        throw new UnexpectedValueException("Unexpected member name “".$name."”");
    }
}
