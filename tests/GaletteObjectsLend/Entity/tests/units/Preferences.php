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
 * Preferences tests
 *
 * @author Johan Cwiklinski <johan@x-tnd.be>
 */
class Preferences extends GaletteTestCase
{
    protected int $seed = 20240519131740;

    /**
     * Test defaults
     *
     * @return void
     */
    public function testDefaults(): void
    {
        $prefs = new \GaletteObjectsLend\Entity\Preferences($this->zdb);
        $this->assertSame(128, $prefs->getThumbWidth());
        $this->assertSame(128, $prefs->getThumbHeight());
        $this->assertTrue($prefs->imagesInLists());
        $this->assertTrue($prefs->showFullsize());

        $all_prefs = $prefs->getPreferences();
        $this->assertCount(17, $all_prefs);
        foreach ($all_prefs as $code => $value) {
            $this->assertSame($value, $prefs->$code);
        }

        $prefs = new \GaletteObjectsLend\Entity\Preferences($this->zdb, false);
        $this->assertTrue($prefs->load());

        $prefs = new \GaletteObjectsLend\Entity\Preferences($this->zdb, false);
        $this->assertCount(17, $prefs->getPreferences());

        $this->expectException(\RuntimeException::class);
        $this->assertSame(null, $prefs->NON_EXISTING);
    }

    /**
     * Test add and update
     *
     * @return void
     */
    public function testCrud(): void
    {
        $prefs = new \GaletteObjectsLend\Entity\Preferences($this->zdb);
        $orig_prefs = $prefs->getPreferences();
        $this->assertCount(17, $orig_prefs);

        $all_prefs = $orig_prefs;

        $this->assertSame(128, (int)$all_prefs[\GaletteObjectsLend\Entity\Preferences::PARAM_THUMB_MAX_WIDTH]);
        $this->assertSame(128, (int)$all_prefs[\GaletteObjectsLend\Entity\Preferences::PARAM_THUMB_MAX_HEIGHT]);
        $all_prefs[\GaletteObjectsLend\Entity\Preferences::PARAM_THUMB_MAX_WIDTH] = 256;
        $all_prefs[\GaletteObjectsLend\Entity\Preferences::PARAM_THUMB_MAX_HEIGHT] = 256;

        $this->assertTrue($prefs->store($all_prefs));
        $all_prefs = $prefs->getPreferences();
        $this->assertCount(17, $all_prefs);
        $this->assertSame(256, (int)$all_prefs[\GaletteObjectsLend\Entity\Preferences::PARAM_THUMB_MAX_WIDTH]);
        $this->assertSame(256, (int)$all_prefs[\GaletteObjectsLend\Entity\Preferences::PARAM_THUMB_MAX_HEIGHT]);

        $this->assertTrue($prefs->store($orig_prefs));
    }
}
