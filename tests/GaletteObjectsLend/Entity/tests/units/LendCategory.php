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
 * Category tests
 *
 * @author Johan Cwiklinski <johan@x-tnd.be>
 */
class LendCategory extends GaletteTestCase
{
    protected int $seed = 20240521212536;

    protected \Galette\Core\Plugins $plugins;

    /**
     * Set up tests
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->plugins = $this->container->get('plugins');
    }

    /**
     * Cleanup after each test method
     *
     * @return void
     */
    public function tearDown(): void
    {
        $delete = $this->zdb->delete(LEND_PREFIX . \GaletteObjectsLend\Entity\LendCategory::TABLE);
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
        $category = new \GaletteObjectsLend\Entity\LendCategory($this->zdb, $this->plugins);
        $this->assertSame('No category (0)', $category->getName());
        $this->assertSame('No category', $category->getName(false));
        $this->assertInstanceOf(\GaletteObjectsLend\Entity\CategoryPicture::class, $category->getPicture());
        $this->assertSame(0.0, $category->getSum());
        $this->assertSame(0, $category->getObjectsNb());
        $this->assertTrue($category->isActive());
        $this->assertNull($category->getId());
        $this->assertSame('0,00', $category->objects_price_sum);
        $this->assertNull($category->non_existing);

        $category = new \GaletteObjectsLend\Entity\LendCategory(
            $this->zdb,
            $this->plugins,
            null,
            ['picture' => false]
        );
        $this->assertNull($category->getPicture());
    }

    /**
     * Test add and update
     *
     * @return void
     */
    public function testCrud(): void
    {
        $category = new \GaletteObjectsLend\Entity\LendCategory($this->zdb, $this->plugins);

        $category->name = 'Test category';
        $category->is_active = false;

        $this->assertTrue($category->store());
        $cid = $category->getId();
        $this->assertGreaterThan(0, $cid);

        $category = new \GaletteObjectsLend\Entity\LendCategory($this->zdb, $this->plugins, $cid);
        $this->assertSame('Test category (0)', $category->getName());
        $this->assertFalse($category->isActive());

        $category->name = 'Test category (edited)';
        $this->assertTrue($category->store());

        $category = new \GaletteObjectsLend\Entity\LendCategory($this->zdb, $this->plugins, $cid);
        $this->assertSame('Test category (edited) (0)', $category->getName());

        $this->assertTrue($category->delete());
        new \GaletteObjectsLend\Entity\LendCategory($this->zdb, $this->plugins, $cid);
    }
}
