<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * ObjectsLend routes
 *
 * PHP version 5
 *
 * Copyright © 2017-2020 The Galette Team
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
 * @category Plugins
 * @package  GaletteObjectsLend
 *
 * @author    Johan Cwiklinski <johan@x-tnd.be>
 * @copyright 2017-2020 The Galette Team
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL License 3.0 or (at your option) any later version
 * @version   SVN: $Id$
 * @link      http://galette.tuxfamily.org
 * @since     2017-11-19
 */

use Analog\Analog;
use Galette\Entity\ContributionsTypes;
use Galette\Entity\Adherent;
use Galette\Entity\Contribution;
use Galette\Repository\Members;
use GaletteObjectsLend\Entity\Preferences;
use GaletteObjectsLend\Entity\ObjectPicture;
use GaletteObjectsLend\Entity\CategoryPicture;
use GaletteObjectsLend\Entity\LendCategory;
use GaletteObjectsLend\Entity\LendStatus;
use GaletteObjectsLend\Entity\LendObject;
use GaletteObjectsLend\Entity\LendRent;
use GaletteObjectsLend\Repository\Categories;
use GaletteObjectsLend\Repository\Objects;
use GaletteObjectsLend\Repository\Status;
use GaletteObjectsLend\Filters\StatusList;
use GaletteObjectsLend\Filters\ObjectsList;
use GaletteObjectsLend\Filters\CategoriesList;
use GaletteObjectsLend\Controllers\Crud\CategoriesController;
use GaletteObjectsLend\Controllers\ImagesController;
use GaletteObjectsLend\Controllers\Crud\StatusController;
use GaletteObjectsLend\Controllers\Crud\ObjectsController;
use GaletteObjectsLend\Controllers\PdfController;
use GaletteObjectsLend\Controllers\MainController;

//Constants and classes from plugin
require_once $module['root'] . '/_config.inc.php';

$this->get(
    '/preferences',
    [MainController::class, 'preferences']
)->setName('objectslend_preferences')->add($authenticate);

$this->post(
    '/preferences',
    [MainController::class, 'storePreferences']
)->setName('store_objectlend_preferences')->add($authenticate);

$this->get(
    '/category/add',
    [CategoriesController::class, 'add']
)->setName('objectslend_category_add')->add($authenticate);

$this->get(
    '/category/edit/{id:\d+}',
    [CategoriesController::class, 'edit']
)->setName('objectslend_category_edit')->add($authenticate);

$this->post(
    '/category/add',
    [CategoriesController::class, 'doAdd']
)->setName('objectslend_category_action_add')->add($authenticate);

$this->post(
    '/category/edit/{id:\d+}',
    [CategoriesController::class, 'doEdit']
)->setName('objectslend_category_action_edit')->add($authenticate);

$this->get(
    '/{type:category|object}/{mode:photo|thumbnail}[/{id:\d+}]',
    [ImagesController::class, 'lendPicture']
)->setName('objectslend_photo');

$this->get(
    '/categories[/{option:page|order}/{value:\d+}]',
    [CategoriesController::class, 'list']
)->setName('objectslend_categories')->add($authenticate);

//categories list filtering
$this->post(
    '/categories/filter',
    [CategoriesController::class, 'filter']
)->setName('objectslend_filter_categories')->add($authenticate);

$this->get(
    '/category/remove/{id:\d+}',
    [CategoriesController::class, 'confirmDelete']
)->setName('objectslend_remove_category')->add($authenticate);

$this->post(
    '/category/remove/{id:\d+}',
    [CategoriesController::class, 'delete']
)->setName('objectslend_doremove_category')->add($authenticate);

$this->get(
    '/status/add',
    [StatusController::class, 'add']
)->setName('objectslend_status_add')->add($authenticate);

$this->get(
    '/status/edit/{id:\d+}',
    [StatusController::class, 'edit']
)->setName('objectslend_status_edit')->add($authenticate);

$this->post(
    '/status/add',
    [StatusController::class, 'doAdd']
)->setName('objectslend_status_action_add')->add($authenticate);

$this->post(
    '/status/edit/{id:\d+}',
    [StatusController::class, 'doEdit']
)->setName('objectslend_status_action_edit')->add($authenticate);

$this->get(
    '/statuses[/{option:page|order}/{value:\d+}]',
    [StatusController::class, 'list']
)->setName('objectslend_statuses')->add($authenticate);

//status list filtering
$this->post(
    '/statuses/filter',
    [StatusController::class, 'filter']
)->setName('objectslend_filter_statuses')->add($authenticate);

$this->get(
    '/status/remove/{id:\d+}',
    [StatusController::class, 'confirmDelete']
)->setName('objectslend_remove_status')->add($authenticate);

$this->post(
    '/status/remove/{id:\d+}',
    [StatusController::class, 'delete']
)->setName('objectslend_doremove_status')->add($authenticate);

$this->get(
    '/object/add',
    [ObjectsController::class, 'add']
)->setName('objectslend_object_add')->add($authenticate);

$this->get(
    '/object/edit/{id:\d+}',
    [ObjectsController::class, 'edit']
)->setName('objectslend_object_edit')->add($authenticate);

$this->get(
    '/object/clone/{id:\d+}',
    [ObjectsController::class, 'doClone']
)->setName('objectslend_object_clone')->add($authenticate);

$this->post(
    '/object/add',
    [ObjectsController::class, 'add']
)->setName('objectslend_object_action_add')->add($authenticate);

$this->post(
    '/object/edit/{id:\d+}',
    [ObjectsController::class, 'doEdit']
)->setName('objectslend_object_action_edit')->add($authenticate);

$this->get(
    '/objects[/{option:page|order|category}/{value:\d+}]',
    [ObjectsController::class, 'list']
)->setName('objectslend_objects')->add($authenticate);

//objects list filtering
$this->post(
    '/objects/filter',
    [ObjectsController::class, 'filter']
)->setName('objectslend_filter_objects')->add($authenticate);

$this->get(
    '/object/remove/{id:\d+}',
    [ObjectsController::class, 'confirmDelete']
)->setName('objectslend_remove_object')->add($authenticate);

$this->post(
    '/object/remove[/{id:\d+}]',
    [ObjectsController::class, 'delete']
)->setName('objectslend_doremove_object')->add($authenticate);

//Batch actions on objects list
$this->post(
    '/objects/batch',
    [ObjectsController::class, 'handleBatch']
)->setName('objectslend_batch-objectslist')->add($authenticate);

$this->get(
    '/objects/remove',
    [ObjectsController::class, 'confirmDelete']
)->setName('objectslend_remove_objects')->add($authenticate);

$this->get(
    '/objects/print',
    [PdfController::class, 'printObjects']
)->setName('objectslend_objects_print')->add($authenticate);

$this->get(
    '/object/print/{id:\d+}',
    [PdfController::class, 'printObject']
)->setName('objectslend_object_print')->add($authenticate);

$this->get(
    '/object/show/{id:\d+}',
    [ObjectsController::class, 'show']
)->setName('objectslend_show_object_lend')->add($authenticate);

$this->get(
    '/object/{action:take|return}/{id:\d+}',
    [ObjectsController::class, 'lend']
)->setName('objectslend_object_take')->add($authenticate);

$this->post(
    '/object/take/{id:\d+}',
    [ObjectsController::class, 'doTake']
)->setName('objectslend_object_dotake')->add($authenticate);

$this->post(
    '/object/return/{id:\d+}',
    [ObjectsController::class, 'doReturn']
)->setName('objectslend_object_doreturn')->add($authenticate);
