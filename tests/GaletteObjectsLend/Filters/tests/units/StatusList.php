<?php

/**
 * Copyright © 2003-2025 The Galette Team
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

use Galette\Tests\GaletteTestCase;

/**
 * Status filters tests class
 *
 * @author Johan Cwiklinski <johan@x-tnd.be>
 */
class StatusList extends GaletteTestCase
{
    /**
     * Test filter defaults values
     *
     * @param \GaletteObjectsLend\Filters\StatusList $filters Filters instance
     *
     * @return void
     */
    protected function testDefaults(\GaletteObjectsLend\Filters\StatusList $filters): void
    {
        $this->assertSame(\GaletteObjectsLend\Repository\Status::ORDERBY_NAME, $filters->orderby);
        $this->assertSame(\Galette\Enums\SQLOrder::ASC->value, $filters->getDirection());
        $this->assertNull($filters->filter_str);
        $this->assertNull($filters->active_filter);
        $this->assertNull($filters->stock_filter);
    }

    /**
     * Test creation
     *
     * @return void
     */
    public function testCreate(): void
    {
        $filters = new \GaletteObjectsLend\Filters\StatusList();

        $this->testDefaults($filters);

        //change order field
        $filters->orderby = \GaletteObjectsLend\Repository\Status::ORDERBY_STOCK;
        $this->assertSame(\GaletteObjectsLend\Repository\Status::ORDERBY_STOCK, $filters->orderby);
        $this->assertSame(\Galette\Enums\SQLOrder::ASC->value, $filters->getDirection());

        //same order field again: direction inverted
        $filters->orderby = \GaletteObjectsLend\Repository\Status::ORDERBY_STOCK;
        $this->assertSame(\GaletteObjectsLend\Repository\Status::ORDERBY_STOCK, $filters->orderby);
        $this->assertSame(\Galette\Enums\SQLOrder::DESC->value, $filters->getDirection());

        //not existing order, same kept
        $filters->setDirection('abcd');
        $this->expectLogEntry(
            \Analog::WARNING,
            '[GaletteObjectsLend\Filters\StatusList|Pagination] "abcd" is not a valid backing value for enum Galette\Enums\SQLOrder'
        );
        $this->assertSame(\GaletteObjectsLend\Repository\Status::ORDERBY_STOCK, $filters->orderby);
        $this->assertSame(\Galette\Enums\SQLOrder::DESC->value, $filters->getDirection());

        //change direction only
        $filters->setDirection(\Galette\Enums\SQLOrder::ASC);
        $this->assertSame(\GaletteObjectsLend\Repository\Status::ORDERBY_STOCK, $filters->orderby);
        $this->assertSame(\Galette\Enums\SQLOrder::ASC->value, $filters->getDirection());

        //set string filter
        $filters->filter_str = 'a string';
        $this->assertSame('a string', $filters->filter_str);

        //Set activity filter
        $filters->active_filter = \GaletteObjectsLend\Repository\Status::INACTIVE;
        $this->assertSame(\GaletteObjectsLend\Repository\Status::INACTIVE, $filters->active_filter);

        //out of known values, no change
        $filters->active_filter = 42;
        $this->expectLogEntry(
            \Analog::WARNING,
            '[StatusList] Value for active filter should be either 1 or 2 (42 given)'
        );
        $this->assertSame(\GaletteObjectsLend\Repository\Status::INACTIVE, $filters->active_filter);

        //set stock filter
        $filters->stock_filter = \GaletteObjectsLend\Repository\Status::IN_STOCK;
        $this->assertSame(\GaletteObjectsLend\Repository\Status::IN_STOCK, $filters->stock_filter);

        $filters->stock_filter = \GaletteObjectsLend\Repository\Status::OUT_STOCK;
        $this->assertSame(\GaletteObjectsLend\Repository\Status::OUT_STOCK, $filters->stock_filter);

        //out of known values, no change
        $filters->stock_filter = 42;
        $this->expectLogEntry(
            \Analog::WARNING,
            '[StatusList] Value for stock filter should be either 1, 2 or 0 (42 given)'
        );
        $this->assertSame(\GaletteObjectsLend\Repository\Status::OUT_STOCK, $filters->stock_filter);

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
        $filters = new \GaletteObjectsLend\Filters\StatusList();
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
        $filters = new \GaletteObjectsLend\Filters\StatusList();
        $this->testDefaults($filters);

        $this->expectException(\RuntimeException::class);
        $value = $filters->non_existing;
    }
}
