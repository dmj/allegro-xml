<?php

/**
 * This file is part of Allegro XML.
 *
 * Allegro XML is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Allegro XML is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Allegro XML.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @author    David Maus <maus@hab.de>
 * @copyright Copyright (c) 2019 by Herzog August Bibliothek Wolfenbüttel
 * @license   http://www.gnu.org/licenses/gpl.txt GNU General Public License v3
 */

namespace HAB\Allegro;

use SplStack;
use ArrayObject;

/**
 * Allegro parser.
 *
 * @author    David Maus <maus@hab.de>
 * @copyright Copyright (c) 2019 by Herzog August Bibliothek Wolfenbüttel
 * @license   http://www.gnu.org/licenses/gpl.txt GNU General Public License v3
 */
class Parser
{
    /**
     * Return array representation of Allegro records.
     *
     * @param  Stream $stream
     * @return array
     */
    public function parse (Stream $stream)
    {
        $records = new SplStack();
        $parsefun = array($this, 'startOfRecord');
        do {
            $parsefun = $parsefun($stream, $records);
        } while ($parsefun && !$stream->eof());
        return iterator_to_array($records);
    }

    private function startOfRecord ($stream, $records)
    {
        $byte = $stream->peek();
        if ($byte === false) {
            return false;
        }
        if ($byte === 1) {
            $marker = $stream->read(3);
            if ($marker[1] === 0 && $marker[2] === 0) {
                $records->push(new ArrayObject());
                return array($this, 'recordNumber');
            }
            throw new RuntimeException("Unexpected character in input stream");
        }
        if ($byte === 10 || $byte === 13) {
            $stream->get();
            return array($this, 'startOfRecord');
        }
        return array($this, 'fieldTag');
    }

    private function recordNumber ($stream, $records)
    {
        $data = $stream->read(2);
        $number = (256 * $data[0]) + $data[1];
        $records->top()->append($number);
        return array($this, 'fieldTag');
    }

    private function fieldTag ($stream, $records)
    {
        $data = $stream->read(3);
        $tag = implode(array_map('chr', $data));
        $records->top()->append($tag);
        return array($this, 'fieldContent');
    }

    private function fieldContent ($stream, $records)
    {
        $content = array();
        while ($stream->peek()) {
            $content[] = $stream->get();
        }
        $stream->get();
        $content = implode(array_map('chr', $content));
        $records->top()->append(iconv('cp437', 'UTF-8//TRANSLIT', $content));
        return array($this, 'startOfRecord');
    }
}
