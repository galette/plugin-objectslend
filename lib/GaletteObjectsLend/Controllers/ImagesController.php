<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Galette objects lend images controller
 *
 * PHP version 5
 *
 * Copyright © 2021 The Galette Team
 *
 * This file is part of Galette (http://galette.tuxfamily.org).
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
 *
 * @category  Entity
 * @package   Galette
 *
 * @author    Johan Cwiklinski <johan@x-tnd.be>
 * @copyright 2021 The Galette Team
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL License 3.0 or (at your option) any later version
 * @link      http://galette.tuxfamily.org */

namespace GaletteObjectsLend\Controllers;

use Galette\Controllers\ImagesController as GImagesController;
use GaletteObjectsLend\Entity\Preferences;
use Slim\Http\Request;
use Slim\Http\Response;
use Galette\Core\Picture;
use Galette\Entity\Adherent;
use Analog\Analog;

/**
 * Galette objects lend images controller
 *
 * @category  Controllers
 * @name      ImageController
 * @package   GaletteObjectsLend
 * @author    Johan Cwiklinski <johan@x-tnd.be>
 * @copyright 2021 The Galette Team
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL License 3.0 or (at your option) any later version
 * @link      http://galette.tuxfamily.org
 */

class ImagesController extends GImagesController
{
    private $lendsprefs;

    /**
     * Objects lends category or object route
     *
     * @param Request  $request  PSR Request
     * @param Response $response PSR Response
     * @param string   $type     Requested type (category or object)
     * @param string   $mode     Either thumbnail or photo
     * @param int      $id       Object id
     *
     * @return Response
     */
    public function lendPicture(Request $request, Response $response, string $type, string $mode, int $id = null): Response
    {
        $class = '\GaletteObjectsLend\Entity\\' .
            ($type == 'category' ? 'CategoryPicture' : 'ObjectPicture');
        $picture = new $class($this->plugins, $id);

        $this->lendsprefs = new Preferences($this->zdb);
        $thumb = false;
        if (!$this->lendsprefs->showFullsize() || $mode == 'thumbnail') {
            //force thumbnail display from preferences
            $thumb = true;
        }

        if ($thumb) {
            return $picture->displayThumb($response, $this->lendsprefs);
        } else {
            return $picture->display($response);
        }
    }
}
