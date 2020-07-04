<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Take an object
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

if (!$login->isLogged()) {
    header('location: ' . GALETTE_BASE_PATH . 'index.php');
    die();
}
require_once '_config.inc.php';

$tpl->assign('page_title', _T("Borrow an object", "objectslend"));

$lendsprefs = new Preferences($zdb);

if (!$lendsprefs->{Preferences::PARAM_ENABLE_MEMBER_RENT_OBJECT} && !($login->isAdmin() || $login->isStaff())) {
    Analog::log(
        'Trying to take an object without appropriate rights! (Object ' .
        $_GET['object_id'] . ', user ' . $login->login . ')',
        Analog::WARNING
    );
    header('location: objects_list.php');
}

//Set the path to the current plugin's templates,
//but backup main Galette's template path before
$orig_template_path = $tpl->template_dir;
$tpl->template_dir = 'templates/' . $preferences->pref_theme;

/**
 * Prise de l'objet -> on valide et retourne à la liste
 */
if (filter_has_var(INPUT_POST, 'yes')) {
    $object_id = intval(filter_input(INPUT_POST, 'object_id'));
    // Fermeture des anciennes locations de l'objet
    LendRent::closeAllRentsForObject($object_id, '');

    // Ajout d'un nouveau statut "objet loué"
    $rent = new LendRent();
    $rent->object_id = $object_id;
    $rent->status_id = filter_input(INPUT_POST, 'status');
    $rent->date_forecast = null;
    $forecast = filter_input(INPUT_POST, 'expected_return');
    if (strlen($forecast) > 6) {
        list($j, $m, $a) = explode('/', $forecast);
        $rent->date_forecast = $a . '-' . $m . '-' . $j;
    }
    if (filter_has_var(INPUT_POST, 'id_adh') && ($login->isAdmin() || $login->isStaff())) {
        $rent->adherent_id = filter_input(INPUT_POST, 'id_adh');
    } else {
        $rent->adherent_id = $login->id;
    }
    $rent->store();

    // Récupération des informations sur l'objet
    $object = new LendObject($object_id);

    // Récupération du prix de location
    $rentprice = $object->value_rent_price;
    if (filter_has_var(INPUT_POST, 'rent_price') && ($login->isAdmin() || $login->isStaff())) {
        $rentprice = floatval(str_replace(' ', '', str_replace(',', '.', filter_input(INPUT_POST, 'rent_price'))));
    }

    // Ajout d'une contribution
    if ($rentprice > 0 && $lendsprefs->{Preferences::PARAM_AUTO_GENERATE_CONTRIBUTION}) {
        $contrib = new Galette\Entity\Contribution();

        $info = str_replace(array('{NAME}', '{DESCRIPTION}', '{SERIAL_NUMBER}', '{PRICE}', '{RENT_PRICE}', '{WEIGHT}', '{DIMENSION}'), array($object->name, $object->description, $object->serial_number, $object->price, $object->rent_price, $object->weight, $object->dimension), $lendsprefs->{Preferences::PARAM_GENERATED_CONTRIB_INFO_TEXT});

        $values = array(
            'montant_cotis' => $rentprice,
            \Galette\Entity\ContributionsTypes::PK => $lendsprefs->{Preferences::PARAM_GENERATED_CONTRIBUTION_TYPE_ID},
            'date_enreg' => date(_T("Y-m-d")),
            'date_debut_cotis' => date(_T("Y-m-d")),
            'type_paiement_cotis' => intval(filter_input(INPUT_POST, 'payment_type')),
            'info_cotis' => $info,
            \Galette\Entity\Adherent::PK => $rent->adherent_id,
        );
        $contrib->check($values, array(), array());
        $contrib->store();
    }

    if (filter_has_var(INPUT_POST, 'mode') && filter_input(INPUT_POST, 'mode') === 'ajax') {
        echo "OK";
        exit;
    } else {
        // Redirection sur la liste des objets
        header('location: objects_list.php?msg=taken');
    }
}

/**
 * Récupération des adhérents actifs
 */
$members = array();
if ($login->isAdmin() || $login->isStaff()) {
    // members
    $m = new Galette\Repository\Members();
    $required_fields = array(
        'id_adh',
        'nom_adh',
        'prenom_adh',
        'pseudo_adh',
    );
    $members = $m->getList(false, $required_fields);
}

$object_id = filter_has_var(INPUT_GET, 'object_id') ? intval(filter_input(INPUT_GET, 'object_id')) : -1;
$ajax = filter_has_var(INPUT_GET, 'mode') ? filter_input(INPUT_GET, 'mode') === 'ajax' : false;

// Vérification que l'objet est bien dispo
$rents = LendRent::getRentsForObjectId($object_id);
if (count($rents) > 0) {
    $last_rent = $rents[0];
    if (!$last_rent->in_stock) {
        header('location: objects_list.php?msg=unavailable');
    }
}

$object = new LendObject($object_id);

$tpl->assign('object', $object);
$tpl->assign('statuses', LendStatus::getActiveTakeAwayStatuses());
$tpl->assign('members', $members);
$tpl->assign('lendsprefs', $lendsprefs->getpreferences());
$tpl->assign('olendsprefs', $lendsprefs);
$tpl->assign('ajax', $ajax);
$tpl->assign('require_calendar', true);
$tpl->assign('year', date('Y'));
$tpl->assign('month', date('m'));
$tpl->assign('day', date('d'));
$tpl->assign('rent_price', str_replace(array(',', ' '), array('.', ''), $object->rent_price));
$tpl->assign('time', time());
$tpl->assign('takeorgive', 'take');

if ($ajax) {
    $tpl->display('take_object.tpl');
} else {
    $content = $tpl->fetch('take_object.tpl', LEND_SMARTY_PREFIX);
    $tpl->assign('content', $content);
    //Set path to main Galette's template
    $tpl->template_dir = $orig_template_path;
    $tpl->display('page.tpl', LEND_SMARTY_PREFIX);
}
