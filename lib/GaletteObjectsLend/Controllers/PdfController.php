<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Galette objects lend PDF controller
 *
 * PHP version 5
 *
 * Copyright © 2021-2023 The Galette Team
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
 * @category  Controllers
 * @package   Galette
 *
 * @author    Johan Cwiklinski <johan@x-tnd.be>
 * @copyright 2021-2023 The Galette Team
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL License 3.0 or (at your option) any later version
 * @link      http://galette.tuxfamily.org */

namespace GaletteObjectsLend\Controllers;

use Galette\Controllers\PdfController as GPdfController;
use GaletteObjectsLend\Entity\Preferences;
use GaletteObjectsLend\Entity\LendObject;
use GaletteObjectsLend\Filters\ObjectsList;
use GaletteObjectsLend\Repository\Objects;
use GaletteObjectsLend\IO\PdfObject;
use GaletteObjectsLend\IO\PdfObjects;
use Slim\Psr7\Request;
use Slim\Psr7\Response;

/**
 * Galette objects lend PDF controller
 *
 * @category  Controllers
 * @name      PdfController
 * @package   GaletteObjectsLend
 * @author    Johan Cwiklinski <johan@x-tnd.be>
 * @copyright 2021-2023 The Galette Team
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL License 3.0 or (at your option) any later version
 * @link      http://galette.tuxfamily.org
 */

class PdfController extends GPdfController
{
    /**
     * Object lends print object
     *
     * @param Request  $request  PSR Request
     * @param Response $response PSR Response
     * @param int      $id       Object ID
     *
     * @return Response
     */
    public function printObject(Request $request, Response $response, int $id): Response
    {
        $deps = [
            'picture' => true,
            'rents' => true,
            'last_rent' => true,
            'status' => true,
            'member' => true,
            'category' => true
        ];
        $object = new LendObject(
            $this->zdb,
            $this->plugins,
            $id,
            $deps
        );

        $lendsprefs = new Preferences($this->zdb);
        $pdf = new PdfObject(
            $this->zdb,
            $this->preferences,
            $lendsprefs
        );
        $pdf->drawCards([$object]);
        return $this->sendResponse($response, $pdf);
    }

    /**
     * Objects lends print objects
     *
     * @param Request  $request  PSR Request
     * @param Response $response PSR Response
     *
     * @return Response
     */
    public function printObjects(Request $request, Response $response): Response
    {
        $lendsprefs = new Preferences($this->zdb);

        if (isset($this->session->objectslend_filter_objects)) {
            $filters =  $this->session->objectslend_filter_objects;
        } else {
            $filters = new ObjectsList();
        }

        $objects = new Objects($this->zdb, $this->plugins, $lendsprefs, $filters);
        $list = $objects->getObjectsList(true, null, true, false);

        $pdf = new PdfObjects(
            $this->zdb,
            $this->preferences,
            $lendsprefs,
            $filters,
            $this->login,
            $this->plugins
        );

        $pdf->drawList($list);
        return $this->sendResponse($response, $pdf);
    }
}
