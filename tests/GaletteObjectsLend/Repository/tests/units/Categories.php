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

namespace GaletteObjectsLends\Repository\tests\units;

use Galette\GaletteTestCase;

/**
 * Categories tests
 *
 * @author Johan Cwiklinski <johan@x-tnd.be>
 */
class Categories extends GaletteTestCase
{
    protected int $seed = 20240525091538;

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
        $delete = $this->zdb->delete(LEND_PREFIX . \GaletteObjectsLend\Entity\LendObject::TABLE);
        $this->zdb->execute($delete);

        $delete = $this->zdb->delete(LEND_PREFIX . \GaletteObjectsLend\Entity\LendCategory::TABLE);
        $this->zdb->execute($delete);
        parent::tearDown();
    }

    /**
     * Test getList
     *
     * @return void
     */
    public function testGetList(): void
    {
        $categories = new \GaletteObjectsLend\Repository\Categories($this->zdb, $this->login, $this->plugins);

        $rs_list = $categories->getList();
        $this->assertInstanceOf(\Laminas\Db\ResultSet\ResultSet::class, $rs_list);
        $this->assertSame(0, $rs_list->count());
        $this->assertSame([], $categories->getList(true));
        $this->assertNull($categories->getCount());

        $this->assertSame([], $categories->getCategoriesList(true));
        $this->assertSame(0, $categories->getCount());

        $category = new \GaletteObjectsLend\Entity\LendCategory($this->zdb, $this->plugins);
        $category->name = 'One category';
        $category->is_active = true;
        $this->assertTrue($category->store());
        $cat_one_id = $category->category_id;

        $category = new \GaletteObjectsLend\Entity\LendCategory($this->zdb, $this->plugins);
        $category->name = 'Another category';
        $category->is_active = true;
        $this->assertTrue($category->store());

        $category = new \GaletteObjectsLend\Entity\LendCategory($this->zdb, $this->plugins);
        $category->name = 'Yet another category';
        $category->is_active = false;
        $this->assertTrue($category->store());

        $filters = new \GaletteObjectsLend\Filters\CategoriesList();
        $categories = new \GaletteObjectsLend\Repository\Categories($this->zdb, $this->login, $this->plugins, $filters);

        $this->assertCount(3, $categories->getCategoriesList(true));
        $this->assertSame(3, $categories->getCount());

        $filters->filter_str = 'category';
        $this->assertCount(3, $categories->getCategoriesList(true));

        $filters->filter_str = 'Yet';
        $this->assertCount(1, $categories->getCategoriesList(true));

        $filters->filter_str = 'noone';
        $this->assertCount(0, $categories->getCategoriesList(true));

        $filters->reinit();
        $filters->not_empty = true;
        $this->assertCount(0, $categories->getCategoriesList(true));

        $object = new \GaletteObjectsLend\Entity\LendObject($this->zdb, $this->plugins);
        $object->name = 'One object';
        $object->category_id = $cat_one_id;
        $this->assertTrue($object->store());

        $this->assertCount(1, $categories->getCategoriesList(true));

        $filters->reinit();
        $filters->active_filter = \GaletteObjectsLend\Repository\Categories::ALL_CATEGORIES;
        $this->assertCount(3, $categories->getCategoriesList(true));

        $filters->active_filter = \GaletteObjectsLend\Repository\Categories::ACTIVE_CATEGORIES;
        $this->assertCount(2, $categories->getCategoriesList(true));

        $filters->active_filter = \GaletteObjectsLend\Repository\Categories::INACTIVE_CATEGORIES;
        $this->assertCount(1, $categories->getCategoriesList(true));
    }
}
