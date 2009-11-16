<?php

/**
 * Hoa Framework
 *
 *
 * @license
 *
 * GNU General Public License
 *
 * This file is part of Hoa Open Accessibility.
 * Copyright (c) 2007, 2009 Ivan ENDERLIN. All rights reserved.
 *
 * HOA Open Accessibility is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * HOA Open Accessibility is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with HOA Open Accessibility; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 *
 *
 * @category    Framework
 * @package     Hoa_File
 * @subpackage  Hoa_File_Temporary_ReadWrite
 *
 */

/**
 * Hoa_Framework
 */
require_once 'Framework.php';

/**
 * Hoa_File_Exception
 */
import('File.Exception');

/**
 * Hoa_File_Temporary
 */
import('File.Link');

/**
 * Hoa_Stream_Io_In
 */
import('Stream.Io.In');

/**
 * Hoa_Stream_Io_Out
 */
import('Stream.Io.Out');

/**
 * Class Hoa_File_Temporary_ReadWrite.
 *
 * File handler.
 *
 * @author      Ivan ENDERLIN <ivan.enderlin@hoa-project.net>
 * @copyright   Copyright (c) 2007, 2009 Ivan ENDERLIN.
 * @license     http://gnu.org/licenses/gpl.txt GNU GPL
 * @since       PHP 5
 * @version     0.3
 * @package     Hoa_File
 * @subpackage  Hoa_File_Temporary_ReadWrite
 */

class          Hoa_File_Temporary_ReadWrite
    extends    Hoa_File_Temporary
    implements Hoa_Stream_Io_In,
               Hoa_Stream_Io_Out {

    /**
     * Open a file.
     *
     * @access  public
     * @param   string  $streamName    Stream name.
     * @param   string  $mode          Open mode, see the parent::MODE_* constants.
     * @param   string  $context       Context ID (please, see the
     *                                 Hoa_Stream_Context class).
     * @return  void
     * @throw   Hoa_Stream_Exception
     */
    public function __construct ( $streamName, $mode = parent::MODE_APPEND_READ_WRITE,
                                  $context = null ) {

        parent::__construct($streamName, $mode, $context);
    }

    /**
     * Open the stream and return the associated resource.
     *
     * @access  protected
     * @param   string              $streamName    Stream name (e.g. path or URL).
     * @param   Hoa_Stream_Context  $context       Context.
     * @return  resource
     * @throw   Hoa_File_Exception_FileDoesNotExist
     * @throw   Hoa_File_Exception
     */
    protected function &open ( $streamName, Hoa_Stream_Context $context = null ) {

        static $createModes = array(
            parent::MODE_READ_WRITE,
            parent::MODE_TRUNCATE_READ_WRITE,
            parent::MODE_APPEND_READ_WRITE,
            parent::MODE_CREATE_READ_WRITE
        );

        if(!in_array($this->getMode(), $createModes))
            throw new Hoa_File_Exception(
                'Open mode are not supported; given %d. Only %s are supported.',
                0, array($this->getMode(), implode(',', $createModes)));

        preg_match('#^(\w+)://#', $streamName, $match);

        if((( isset($match[1])
           && $match[1] == 'file')
           || !isset($match[1]))
           && !file_exists($streamName))
            throw new Hoa_File_Exception_FileDoesNotExist(
                'File %s does not exist.', 0, $streamName);

        $out = parent::open($streamName, $context);

        return $out;
    }

    /**
     * Test for end-of-file.
     *
     * @access  public
     * @return  bool
     */
    public function eof ( ) {

        return feof($this->getStream());
    }

    /**
     * Read n characters.
     *
     * @access  public
     * @param   int     $length    Length.
     * @return  string
     * @throw   Hoa_File_Exception
     */
    public function read ( $length ) {

        if($length <= 0)
            throw new Hoa_File_Exception(
                'Length must be greather than 0, given %d.', 3, $length);

        return fread($this->getStream(), $length);
    }

    /**
     * Alias of $this->read().
     *
     * @access  public
     * @param   int     $length    Length.
     * @return  string
     */
    public function readString ( $length ) {

        return $this->read($length);
    }

    /**
     * Read a character.
     *
     * @access  public
     * @return  string
     */
    public function readCharacter ( ) {

        return fgetc($this->getStream());
    }

    /**
     * Read an integer.
     *
     * @access  public
     * @param   int     $length    Length.
     * @return  int
     */
    public function readInteger ( $length = 1 ) {

        return (int) $this->read($length);
    }

    /**
     * Read a float.
     *
     * @access  public
     * @param   int     $length    Length.
     * @return  float
     */
    public function readFloat ( $length = 1 ) {

        return (float) $this->read($length);
    }

    /**
     * Read an array.
     * Alias of the $this->scanf() method.
     *
     * @access  public
     * @param   string  $format    Format (see printf's formats).
     * @return  array
     */
    public function readArray ( $format ) {

        return $this->scanf($format);
    }

    /**
     * Read a line.
     *
     * @access  public
     * @return  string
     */
    public function readLine ( ) {

        return fgets($this->getStream());
    }

    /**
     * Read all, i.e. read as much as possible.
     *
     * @access  public
     * @return  string
     */
    public function readAll ( ) {

        if(true === $this->isStreamResourceMustBeUsed()) {

            $current = $this->tell();
            $this->seek(0, Hoa_Stream_Io_Pointable::SEEK_END);
            $end     = $this->tell();
            $this->seek($current, Hoa_Stream_Io_Pointable::SEEK_SET);

            return $this->read($end - $current);
        }

        if(PHP_VERSION_ID < 60000)
            $second = true;
        else
            $second = 0;

        if(null === $this->getStreamContext())
            $third  = null;
        else
            $third  = $this->getStreamContext()->getContext();

        return file_get_contents(
            $this->getStreamName(),
            $second,
            $third,
            $this->tell()
        );
    }

    /**
     * Parse input from a stream according to a format.
     *
     * @access  public
     * @param   string  $format    Format (see printf's formats).
     * @return  array
     */
    public function scanf ( $format ) {

        return fscanf($this->getStream(), $format);
    }

    /**
     * Write n characters.
     *
     * @access  public
     * @param   string  $string    String.
     * @param   int     $length    Length.
     * @return  mixed
     */
    public function write ( $string, $length ) {

        return fwrite($this->getStream(), $string, $length);
    }

    /**
     * Write a string.
     *
     * @access  public
     * @param   string  $string    String.
     * @return  mixed
     */
    public function writeString ( $string ) {

        $string = (string) $string;

        return $this->write($string, strlen($string));
    }

    /**
     * Write a character.
     *
     * @access  public
     * @param   string  $char    Character.
     * @return  mixed
     */
    public function writeCharacter ( $char ) {

        return $this->write((string) $char[0], 1);
    }

    /**
     * Write an integer.
     *
     * @access  public
     * @param   int     $integer    Integer.
     * @return  mixed
     */
    public function writeInteger ( $integer ) {

        $integer = (string) (int) $integer;

        return $this->write($integer, strlen($integer));
    }

    /**
     * Write a float.
     *
     * @access  public
     * @param   float   $float    Float.
     * @return  mixed
     */
    public function writeFloat ( $float ) {

        $float = (string) (float) $float;

        return $this->write($float, strlen($float));
    }

    /**
     * Write an array.
     *
     * @access  public
     * @param   array   $array    Array.
     * @return  mixed
     */
    public function writeArray ( Array $array ) {

        $array = var_export($array, true);

        return $this->write($array, strlen($array));
    }

    /**
     * Write a line.
     *
     * @access  public
     * @param   string  $line    Line.
     * @return  mixed
     */
    public function writeLine ( $line ) {

        if(false === $n = strpos($line, "\n"))
            return $this->write($line, strlen($line));

        return $this->write(substr($line, 0, $n), $n);
    }

    /**
     * Write all, i.e. as much as possible.
     *
     * @access  public
     * @param   string  $string    String.
     * @return  mixed
     */
    public function writeAll ( $string ) {

        return $this->write($string, strlen($string));
    }

    /**
     * Truncate a file to a given length.
     *
     * @access  public
     * @param   int     $size    Size.
     * @return  bool
     */
    public function truncate ( $size ) {

        return ftruncate($this->getStream(), $size);
    }
}