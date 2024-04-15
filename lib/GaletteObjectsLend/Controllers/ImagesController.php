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

namespace GaletteObjectsLend\Controllers;

use Galette\Controllers\ImagesController as GImagesController;
use GaletteObjectsLend\Entity\Preferences;
use Slim\Psr7\Request;
use Slim\Psr7\Response;

/**
 * Galette objects lend images controller
 *
 * @author Johan Cwiklinski <johan@x-tnd.be>
 */

class ImagesController extends GImagesController
{
    private Preferences $lendsprefs;

    /**
     * Objects lends category or object route
     *
     * @param Request  $request  PSR Request
     * @param Response $response PSR Response
     * @param string   $type     Requested type (category or object)
     * @param string   $mode     Either thumbnail or photo
     * @param ?int     $id       Object id
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
