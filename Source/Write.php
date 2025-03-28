<?php

declare(strict_types=1);

/**
 * Hoa
 *
 *
 * @license
 *
 * New BSD License
 *
 * Copyright © 2007-2017, Hoa community. All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *     * Redistributions of source code must retain the above copyright
 *       notice, this list of conditions and the following disclaimer.
 *     * Redistributions in binary form must reproduce the above copyright
 *       notice, this list of conditions and the following disclaimer in the
 *       documentation and/or other materials provided with the distribution.
 *     * Neither the name of the Hoa nor the names of its contributors may be
 *       used to endorse or promote products derived from this software without
 *       specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDERS AND CONTRIBUTORS BE
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 */

namespace Hoa\File;

use Hoa\Stream;

/**
 * Class \Hoa\File\Write.
 *
 * File handler.
 */
class Write extends File implements Stream\IStream\Out
{
    /**
     * Open a file.
     */
    public function __construct(
        string $streamName,
        string $mode    = parent::MODE_APPEND_WRITE,
        ?string $context = null,
        bool $wait      = false
    ) {
        parent::__construct($streamName, $mode, $context, $wait);

        return;
    }

    /**
     * Open the stream and return the associated resource.
     */
    protected function &_open(string $streamName, ?Stream\Context $context = null)
    {
        static $createModes = [
            parent::MODE_TRUNCATE_WRITE,
            parent::MODE_APPEND_WRITE,
            parent::MODE_CREATE_WRITE
        ];

        if (!in_array($this->getMode(), $createModes)) {
            throw new Exception(
                'Open mode are not supported; given %d. Only %s are supported.',
                0,
                [$this->getMode(), implode(', ', $createModes)]
            );
        }

        preg_match('#^(\w+)://#', $streamName, $match);

        if (((isset($match[1]) && $match[1] == 'file') || !isset($match[1])) &&
            !file_exists($streamName) &&
            parent::MODE_TRUNCATE_WRITE == $this->getMode()) {
            throw new Exception\FileDoesNotExist(
                'File %s does not exist.',
                1,
                $streamName
            );
        }

        $out = parent::_open($streamName, $context);

        return $out;
    }

    /**
     * Write n characters.
     */
    public function write(string $string, int $length)
    {
        if (0 > $length) {
            throw new Exception(
                'Length must be greater than 0, given %d.',
                2,
                $length
            );
        }

        return fwrite($this->getStream(), $string, $length);
    }

    /**
     * Write a string.
     */
    public function writeString(string $string)
    {
        $string = (string) $string;

        return $this->write($string, strlen($string));
    }

    /**
     * Write a character.
     */
    public function writeCharacter(string $char)
    {
        return $this->write((string) $char[0], 1);
    }

    /**
     * Write a boolean.
     */
    public function writeBoolean(bool $boolean)
    {
        return $this->write((string) (bool) $boolean, 1);
    }

    /**
     * Write an integer.
     */
    public function writeInteger(int $integer)
    {
        $integer = (string) (int) $integer;

        return $this->write($integer, strlen($integer));
    }

    /**
     * Write a float.
     */
    public function writeFloat(float $float)
    {
        $float = (string) (float) $float;

        return $this->write($float, strlen($float));
    }

    /**
     * Write an array.
     */
    public function writeArray(array $array)
    {
        $array = var_export($array, true);

        return $this->write($array, strlen($array));
    }

    /**
     * Write a line.
     */
    public function writeLine(string $line)
    {
        if (false === $n = strpos($line, "\n")) {
            return $this->write($line . "\n", strlen($line) + 1);
        }

        ++$n;

        return $this->write(substr($line, 0, $n), $n);
    }

    /**
     * Write all, i.e. as much as possible.
     */
    public function writeAll(string $string)
    {
        return $this->write($string, strlen($string));
    }

    /**
     * Truncate a file to a given length.
     */
    public function truncate(int $size): bool
    {
        return ftruncate($this->getStream(), $size);
    }
}
