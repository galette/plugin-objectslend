<?php

/**
 * Copyright © 2003-2024 The Galette Team
 *
 * This file is part of Galette (https://galette.eu).
 *
 * Galette is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Galette is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Galette. If not, see <http://www.gnu.org/licenses/>.
 */

declare(strict_types=1);

namespace GaletteObjectslend\Filters\test\units;

use Galette\GaletteTestCase;

/**
 * Objects filters tests class
 *
 * @author Johan Cwiklinski <johan@x-tnd.be>
 */
class ObjectsList extends GaletteTestCase
{
    /**
     * Test filter defaults values
     *
     * @param \GaletteObjectsLend\Filters\ObjectsList $filters Filters instance
     *
     * @return void
     */
    protected function testDefaults(\GaletteObjectsLend\Filters\ObjectsList $filters): void
    {
        $this->assertSame(\GaletteObjectsLend\Repository\Objects::ORDERBY_NAME, $filters->orderby);
        $this->assertSame(\GaletteObjectsLend\Filters\ObjectsList::ORDER_ASC, $filters->ordered);
        $this->assertNull($filters->filter_str);
        $this->assertNull($filters->category_filter);
        $this->assertNull($filters->active_filter);
        $this->assertNull($filters->field_filter);
        $this->assertSame([], $filters->selected);
    }

    /**
     * Test creation
     *
     * @return void
     */
    public function testCreate(): void
    {
        $filters = new \GaletteObjectsLend\Filters\ObjectsList();

        $this->testDefaults($filters);

        //change order field
        $filters->orderby = \GaletteObjectsLend\Repository\Objects::ORDERBY_STATUS;
        $this->assertSame(\GaletteObjectsLend\Repository\Objects::ORDERBY_STATUS, $filters->orderby);
        $this->assertSame(\GaletteObjectsLend\Filters\ObjectsList::ORDER_ASC, $filters->ordered);

        //same order field again: direction inverted
        $filters->orderby = \GaletteObjectsLend\Repository\Objects::ORDERBY_STATUS;
        $this->assertSame(\GaletteObjectsLend\Repository\Objects::ORDERBY_STATUS, $filters->orderby);
        $this->assertSame(\GaletteObjectsLend\Filters\ObjectsList::ORDER_DESC, $filters->ordered);

        //not existing order, same kept
        $filters->ordered = 42;
        $this->assertSame(\GaletteObjectsLend\Repository\Objects::ORDERBY_STATUS, $filters->orderby);
        $this->assertSame(\GaletteObjectsLend\Filters\ObjectsList::ORDER_DESC, $filters->ordered);

        //change direction only
        $filters->ordered = \GaletteObjectsLend\Filters\ObjectsList::ORDER_ASC;
        $this->assertSame(\GaletteObjectsLend\Repository\Objects::ORDERBY_STATUS, $filters->orderby);
        $this->assertSame(\GaletteObjectsLend\Filters\ObjectsList::ORDER_ASC, $filters->ordered);

        //set string filter
        $filters->filter_str = 'a string';
        $this->assertSame('a string', $filters->filter_str);

        //Set activity filter
        $filters->active_filter = \GaletteObjectsLend\Repository\Objects::INACTIVE_OBJECTS;
        $this->assertSame(\GaletteObjectsLend\Repository\Objects::INACTIVE_OBJECTS, $filters->active_filter);

        //cast is forced
        $filters->active_filter = (string)\GaletteObjectsLend\Repository\Objects::INACTIVE_OBJECTS;
        $this->assertSame(\GaletteObjectsLend\Repository\Objects::INACTIVE_OBJECTS, $filters->active_filter);

        //out of known values, no change
        $filters->active_filter = 42;
        $this->assertSame(\GaletteObjectsLend\Repository\Objects::INACTIVE_OBJECTS, $filters->active_filter);

        $filters->field_filter = \GaletteObjectsLend\Repository\Objects::FILTER_SERIAL;
        $this->assertSame(\GaletteObjectsLend\Repository\Objects::FILTER_SERIAL, $filters->field_filter);

        //cast is forced
        $filters->field_filter = (string)\GaletteObjectsLend\Repository\Objects::FILTER_SERIAL;
        $this->assertSame(\GaletteObjectsLend\Repository\Objects::FILTER_SERIAL, $filters->field_filter);

        //non numeric value, no change
        $filters->field_filter = 'abc';
        $this->assertSame(\GaletteObjectsLend\Repository\Objects::FILTER_SERIAL, $filters->field_filter);

        //reinit and test defaults are back
        $filters->reinit();
        $this->testDefaults($filters);
    }

    /**
     * Test setting non existing filter
     *
     * @return void
     */
    public function testSetNotExisting()
    {
        $filters = new \GaletteObjectsLend\Filters\ObjectsList();
        $this->testDefaults($filters);

        $this->expectException(\RuntimeException::class);
        $filters->non_existing = 42;
    }

    /**
     * Test getting non existing filter
     *
     * @return void
     */
    public function testGetNotExisting()
    {
        $filters = new \GaletteObjectsLend\Filters\ObjectsList();
        $this->testDefaults($filters);

        $this->expectException(\RuntimeException::class);
        $value = $filters->non_existing;
    }
}
