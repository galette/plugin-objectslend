<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Plugin Galette Objects Lend
 *
 * PHP version 5
 *
 * Copyright © 2022 The Galette Team
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
 * @category  Plugins
 * @package   GaletteObjectsLend
 *
 * @author    Johan Cwiklinski <johan@x-tnd.be>
 * @copyright 2022 The Galette Team
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL License 3.0 or (at your option) any later version
 * @version   SVN: $Id$
 * @link      http://galette.tuxfamily.org
 * @since     Available since 2.0.0dev - 2022-05-29
 */

namespace GaletteObjectsLend;

use Galette\Entity\Adherent;
use Galette\Core\GalettePlugin;

/**
 * Plugin Galette Objects Lend
 *
 * @category  Plugins
 * @name      PluginObjectsLend
 * @package   GaletteObjectsLend
 * @author    Johan Cwiklinski <johan@x-tnd.be>
 * @copyright 2022 The Galette Team
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL License 3.0 or (at your option) any later version
 * @link      http://galette.tuxfamily.org
 * @since     Available since 2.0.0dev - 2022-05-29
 */

class PluginGaletteObjectslend extends GalettePlugin
{
    /**
     * Extra menus entries
     *
     * @return array|array[]
     */
    public static function getMenusContents(): array
    {
        /** @var Login $login */
        global $login;
        $menus = [];

        $menus['galetteplugin_objectslends'] = [
            'title' => _T("Objects lend", "objectslend"),
            'icon' => 'briefcase',
            'items' => [
                [
                    'label' => _T("Objects list", "objectslend"),
                    'title' => _T("Objects list", "objectslend"),
                    'route' => [
                        'name' => 'objectslend_objects',
                        'aliases' => ['objectslend_object_add', 'objectslend_object_edit']
                    ]
                ],
            ]
        ];

        if ($login->isAdmin() || $login->isStaff()) {
            $menus['galetteplugin_objectslends']['items'] = array_merge(
                $menus['galetteplugin_objectslends']['items'],
                [
                    [
                        'label' => _T("Borrow status", "objectslend"),
                        'title' => _T("Borrow status", "objectslend"),
                        'route' => [
                            'name' => 'objectslend_statuses',
                            'aliases' => ['objectslend_status_add', 'objectslend_status_edit']
                        ]
                    ],
                    [
                        'label' => _T("Object categories", "objectslend"),
                        'title' => _T("Object categories", "objectslend"),
                        'route' => [
                            'name' => 'objectslend_categories',
                            'aliases' => ['objectslend_category_add', 'objectslend_category_edit']
                        ]
                    ],
                    [
                        'label' => _T("Preferences", "objectslend"),
                        'title' => _T("Preferences", "objectslend"),
                        'route' => [
                            'name' => 'objectslend_preferences'
                        ]
                    ]
                ]
            );
        }

        return $menus;
    }

    /**
     * Extra public menus entries
     *
     * @return array|array[]
     */
    public static function getPublicMenusItemsList(): array
    {
        return [];
    }

    /**
     * Get dashboards contents
     *
     * @return array|array[]
     */
    public static function getDashboardsContents(): array
    {
        return [];
    }

    /**
     * Get actions contents
     *
     * @param Adherent $member Member instance
     *
     * @return array|array[]
     */
    public static function getListActionsContents(Adherent $member): array
    {
        return [];
    }

    /**
     * Get detailed actions contents
     *
     * @param Adherent $member Member instance
     *
     * @return array|array[]
     */
    public static function getDetailedActionsContents(Adherent $member): array
    {
        return static::getListActionsContents($member);
    }

    /**
     * Get batch actions contents
     *
     * @return array|array[]
     */
    public static function getBatchActionsContents(): array
    {
        return [];
    }
}
