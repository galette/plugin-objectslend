<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Give more objects back
 *
 * PHP version 5
 *
 * Copyright © 2013-2016 Mélissa Djebel
 * Copyright © 2017 The Galette Team
 *
 * This file is part of Galette (http://galette.tuxfamily.org).
 *
 * ObjectsLend is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * ObjectsLend is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Galette. If not, see <http://www.gnu.org/licenses/>.
 *
 * @category  Plugins
 * @package   ObjectsLend
 *
 * @author    Mélissa Djebel <melissa.djebel@gmx.net>
 * @author    Johan Cwiklinski <johan@x-tnd.be>
 * @copyright 2013-2016 Mélissa Djebel
 * Copyright © 2017 The Galette Team
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL License 3.0 or (at your option) any later version
 * @version   0.7
 * @link      http://galette.tuxfamily.org
 * @since     Available since 0.7
 */

use GaletteObjectsLend\LendObject;
use GaletteObjectsLend\Preferences;
use GaletteObjectsLend\LendRent;
use GaletteObjectsLend\LendStatus;

define('GALETTE_BASE_PATH', '../../');
require_once GALETTE_BASE_PATH . 'includes/galette.inc.php';
if (!$login->isLogged() && !($login->isAdmin() || $login->isStaff())) {
    header('location: ' . GALETTE_BASE_PATH . 'index.php');
    die();
}
require_once '_config.inc.php';

$lendsprefs = new Preferences($zdb);

/*
 * Traitement résultats
 */
if (filter_has_var(INPUT_POST, 'yes')) {
    $objects_ids = $_POST['objects_id'];
    foreach ($objects_ids as $o_id) {
        LendRent::closeAllRentsForObject($o_id, filter_input(INPUT_POST, 'comments'));
        $rent = new LendRent();
        $rent->object_id = $o_id;
        $rent->status_id = filter_input(INPUT_POST, 'status');
        $rent->store();
    }

    if (filter_has_var(INPUT_POST, 'mode') && filter_input(INPUT_POST, 'mode') === 'ajax') {
        echo "OK";
        exit;
    } else {
        header('location: objects_list.php?msg=given');
    }
}

$tpl->assign('page_title', _T("Give back objects", "objectslend"));
//Set the path to the current plugin's templates,
//but backup main Galette's template path before
$orig_template_path = $tpl->template_dir;
$tpl->template_dir = 'templates/' . $preferences->pref_theme;

$objects_ids = filter_has_var(INPUT_GET, 'objects_ids') ? explode(',', filter_input(INPUT_GET, 'objects_ids')) : array();
$ajax = filter_has_var(INPUT_GET, 'mode') ? filter_input(INPUT_GET, 'mode') === 'ajax' : false;

/**
 * Récupération des objets et vérification qu'ils sont bien sortis
 */
$objects = array();
foreach (LendObject::getMoreObjectsByIds($objects_ids) as $obj) {
    if (!$obj->in_stock) {
        $objects[] = $obj;
    }
}

$tpl->assign('objects', $objects);

$tpl->assign('statuses', LendStatus::getActiveHomeStatuses());

$tpl->assign('ajax', $ajax);
$tpl->assign('lendsprefs', $lendsprefs->getpreferences());
$tpl->assign('olendsprefs', $lendsprefs);
$tpl->assign('takeorgive', 'give');
$tpl->assign('time', time());

if ($ajax) {
    $tpl->display('take_more_objects_away.tpl', LEND_SMARTY_PREFIX);
} else {
    $content = $tpl->fetch('take_more_objects_away.tpl', LEND_SMARTY_PREFIX);
    $tpl->assign('content', $content);
    //Set path to main Galette's template
    $tpl->template_dir = $orig_template_path;
    $tpl->display('page.tpl', LEND_SMARTY_PREFIX);
}
