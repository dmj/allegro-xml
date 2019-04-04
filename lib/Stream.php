<?php

/**
 * This file is part of Allegro XML.
 *
 * SimpleMARC is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * SimpleMARC is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with SimpleMARC.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @author    David Maus <maus@hab.de>
 * @copyright Copyright (c) 2019 by Herzog August Bibliothek Wolfenbüttel
 * @license   http://www.gnu.org/licenses/gpl.txt GNU General Public License v3
 */

namespace HAB\Allegro;

/**
 * Stream implementation.
 *
 * @author    David Maus <maus@hab.de>
 * @copyright Copyright (c) 2019 by Herzog August Bibliothek Wolfenbüttel
 * @license   http://www.gnu.org/licenses/gpl.txt GNU General Public License v3
 */
class Stream
{
    /**
     * Buffered stream data.
     *
     * @var array
     */
    private $buffer;

    /**
     * Number of bytes in stream data buffer.
     *
     * @var int
     */
    private $bufferSize = 0;

    /**
     * Cursor position in stream data buffer.
     *
     * @var int
     */
    private $bufferPosition = 0;

    /**
     * Stream resource.
     *
     * @var resource
     */
    private $resource;

    /**
     * Byte at the tip of the stream.
     *
     * @var int
     */
    private $tip = false;

    public function __construct ($resource)
    {
        $this->resource = $resource;
    }

    /**
     * Read specified number of bytes from stream.
     *
     * @throws PrematureEndOfStream
     *
     * @param  int $length
     * @return array
     */
    public function read ($length)
    {
        $data = array();
        for ($i = 0; $i < $length; $i++) {
            $byte = $this->get();
            if ($byte === false) {
                break;
            }
            $data[] = $byte;
        }
        if (count($data) !== $length) {
            throw new PrematureEndOfStream();
        }
        return $data;
    }

    /**
     * Return true if the stream is exhausted.
     *
     * @return boolean
     */
    public function eof ()
    {
        return feof($this->resource);
    }

    /**
     * Peek at the tip of the stream.
     *
     * @return integer
     */
    public function peek ()
    {
        if ($this->tip === false) {
            $this->tip = $this->get();
        }
        return $this->tip;
    }

    /**
     * Get single byte from stream.
     *
     * @return integer
     */
    public function get ()
    {
        if ($this->bufferPosition >= $this->bufferSize) {
            if (feof($this->resource)) {
                return false;
            }
            $this->buffer = array_values(unpack('C*', fread($this->resource, 4096)));
            if (empty($this->buffer)) {
                return false;
            }
            $this->bufferSize = count($this->buffer);
            $this->bufferPosition = 0;
        }
        if ($this->tip === false) {
            $byte = $this->buffer[$this->bufferPosition];
            $this->bufferPosition += 1;
        } else {
            $byte = $this->tip;
            $this->tip = false;
        }
        return $byte;
    }
}
