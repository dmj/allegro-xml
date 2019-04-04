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

/**
 * Serialize Allegro records.
 *
 * @author    David Maus <maus@hab.de>
 * @copyright Copyright (c) 2019 by Herzog August Bibliothek Wolfenbüttel
 * @license   http://www.gnu.org/licenses/gpl.txt GNU General Public License v3
 */
class Serializer
{
    public function serialize (iterable $collection)
    {
        $buffer = array();
        $buffer []= '<collection xmlns="tag:maus@hab.de,2019:Allegro">';
        foreach ($collection as $record) {
            $buffer[]= sprintf('<record number="%d">', $record[0]);
            for ($i = 1; $i < count($record); $i += 2) {
                $tag = htmlspecialchars($record[$i], ENT_XML1 | ENT_QUOTES | ENT_DISALLOWED);
                $cnt = htmlspecialchars($record[$i + 1], ENT_XML1 | ENT_QUOTES | ENT_DISALLOWED);
                $buffer[]= sprintf('<field tag="%s">%s</field>', $tag, $cnt);
            }
            $buffer[]= '</record>';
        }
        $buffer []= '</collection>';
        return implode($buffer);
    }
}
