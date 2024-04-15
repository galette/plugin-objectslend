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

namespace GaletteObjectsLend\Entity;

use Analog\Analog;
use Galette\Core\Plugins;

/**
 * Picture for category
 *
 * @author Mélissa Djebel <melissa.djebel@gmx.net>
 * @author Johan Cwiklinski <johan@x-tnd.be>
 */
class CategoryPicture extends Picture
{
    public const PK = 'category_id';
    public const TABLE = 'categories_pictures';

    /**
     * Default constructor.
     *
     * @param Plugins    $plugins  Plugins
     * @param mixed|null $objectid Object id
     */
    public function __construct(Plugins $plugins, mixed $objectid = null)
    {
        $this->store_path = GALETTE_PHOTOS_PATH . 'objectslend/categories/';
        parent::__construct($plugins, $objectid);
    }
}
