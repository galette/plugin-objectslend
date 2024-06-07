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

namespace GaletteObjectsLends\Entity\tests\units;

use Galette\GaletteTestCase;

/**
 * Status tests
 *
 * @author Johan Cwiklinski <johan@x-tnd.be>
 */
class LendStatus extends GaletteTestCase
{
    protected int $seed = 20240521230915;

    /**
     * Cleanup after each test method
     *
     * @return void
     */
    public function tearDown(): void
    {
        $delete = $this->zdb->delete(LEND_PREFIX . \GaletteObjectsLend\Entity\LendStatus::TABLE);
        $this->zdb->execute($delete);
        parent::tearDown();
    }

    /**
     * Test empty
     *
     * @return void
     */
    public function testEmpty(): void
    {
        $status = new \GaletteObjectsLend\Entity\LendStatus($this->zdb);
        $this->assertNull($status->status_id);
        $this->assertSame('', $status->status_text);
        $this->assertFalse($status->in_stock);
        $this->assertTrue($status->is_active);
        $this->assertNull($status->rent_day_number);
    }

    /**
     * Test add and update
     *
     * @return void
     */
    public function testCrud(): void
    {
        $status = new \GaletteObjectsLend\Entity\LendStatus($this->zdb);
        $status->status_text = 'One active status';
        $status->in_stock = true;
        $status->is_active = true;
        $this->assertTrue($status->store());
        $status_one = $status->status_id;

        $status = new \GaletteObjectsLend\Entity\LendStatus($this->zdb);
        $status->status_text = 'Another active status';
        $status->in_stock = false;
        $status->is_active = true;
        $this->assertTrue($status->store());
        $status_two = $status->status_id;

        $status = new \GaletteObjectsLend\Entity\LendStatus($this->zdb);
        $status->status_text = 'One inactive status';
        $status->in_stock = true;
        $status->is_active = false;
        $this->assertTrue($status->store());
        $status_three = $status->status_id;

        $list = $status::getActiveTakeAwayStatuses($this->zdb);
        $this->assertCount(1, $list);

        $active_one = $list[0];
        $this->assertSame($status_two, $active_one->status_id);
        $this->assertSame('Another active status', $active_one->status_text);

        $list = $status::getActiveStockStatuses($this->zdb);
        $this->assertCount(1, $list);

        $active_one = $list[0];
        $this->assertSame($status_one, $active_one->status_id);
        $this->assertSame('One active status', $active_one->status_text);

        $list = $status::getActiveStatuses($this->zdb);
        $this->assertCount(2, $list);

        $status = new \GaletteObjectsLend\Entity\LendStatus($this->zdb, $status_one);
        $status->status_text = 'One active status (edited)';
        $this->assertTrue($status->store());

        $status = new \GaletteObjectsLend\Entity\LendStatus($this->zdb, $status_one);
        $this->assertSame('One active status (edited)', $status->status_text);

        $this->assertTrue($status->delete());
        $status = new \GaletteObjectsLend\Entity\LendStatus($this->zdb, $status_one);
        $this->assertNull($status->status_id);
    }
}
