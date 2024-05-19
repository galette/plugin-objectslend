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

declare(strict_types=1);

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

$app->get(
    '/preferences',
    [MainController::class, 'preferences']
)->setName('objectslend_preferences')->add($authenticate);

$app->post(
    '/preferences',
    [MainController::class, 'storePreferences']
)->setName('store_objectlend_preferences')->add($authenticate);

$app->get(
    '/category/add',
    [CategoriesController::class, 'add']
)->setName('objectslend_category_add')->add($authenticate);

$app->get(
    '/category/edit/{id:\d+}',
    [CategoriesController::class, 'edit']
)->setName('objectslend_category_edit')->add($authenticate);

$app->post(
    '/category/add',
    [CategoriesController::class, 'doAdd']
)->setName('objectslend_category_action_add')->add($authenticate);

$app->post(
    '/category/edit/{id:\d+}',
    [CategoriesController::class, 'doEdit']
)->setName('objectslend_category_action_edit')->add($authenticate);

$app->get(
    '/{type:category|object}/{mode:photo|thumbnail}[/{id:\d+}]',
    [ImagesController::class, 'lendPicture']
)->setName('objectslend_photo');

$app->get(
    '/categories[/{option:page|order}/{value:\d+}]',
    [CategoriesController::class, 'list']
)->setName('objectslend_categories')->add($authenticate);

//categories list filtering
$app->post(
    '/categories/filter',
    [CategoriesController::class, 'filter']
)->setName('objectslend_filter_categories')->add($authenticate);

$app->get(
    '/category/remove/{id:\d+}',
    [CategoriesController::class, 'confirmDelete']
)->setName('objectslend_remove_category')->add($authenticate);

$app->post(
    '/category/remove/{id:\d+}',
    [CategoriesController::class, 'delete']
)->setName('objectslend_doremove_category')->add($authenticate);

$app->get(
    '/status/add',
    [StatusController::class, 'add']
)->setName('objectslend_status_add')->add($authenticate);

$app->get(
    '/status/edit/{id:\d+}',
    [StatusController::class, 'edit']
)->setName('objectslend_status_edit')->add($authenticate);

$app->post(
    '/status/add',
    [StatusController::class, 'doAdd']
)->setName('objectslend_status_action_add')->add($authenticate);

$app->post(
    '/status/edit/{id:\d+}',
    [StatusController::class, 'doEdit']
)->setName('objectslend_status_action_edit')->add($authenticate);

$app->get(
    '/statuses[/{option:page|order}/{value:\d+}]',
    [StatusController::class, 'list']
)->setName('objectslend_statuses')->add($authenticate);

//status list filtering
$app->post(
    '/statuses/filter',
    [StatusController::class, 'filter']
)->setName('objectslend_filter_statuses')->add($authenticate);

$app->get(
    '/status/remove/{id:\d+}',
    [StatusController::class, 'confirmDelete']
)->setName('objectslend_remove_status')->add($authenticate);

$app->post(
    '/status/remove/{id:\d+}',
    [StatusController::class, 'delete']
)->setName('objectslend_doremove_status')->add($authenticate);

$app->get(
    '/object/add',
    [ObjectsController::class, 'add']
)->setName('objectslend_object_add')->add($authenticate);

$app->get(
    '/object/edit/{id:\d+}',
    [ObjectsController::class, 'edit']
)->setName('objectslend_object_edit')->add($authenticate);

$app->post(
    '/object/{id:\d+}/updatestatus',
    [ObjectsController::class, 'doUpdateStatus']
)->setName('objectslend_object_updatestatus')->add($authenticate);


$app->get(
    '/object/clone/{id:\d+}',
    [ObjectsController::class, 'doClone']
)->setName('objectslend_object_clone')->add($authenticate);

$app->post(
    '/object/add',
    [ObjectsController::class, 'doAdd']
)->setName('objectslend_object_action_add')->add($authenticate);

$app->post(
    '/object/edit/{id:\d+}',
    [ObjectsController::class, 'doEdit']
)->setName('objectslend_object_action_edit')->add($authenticate);

$app->get(
    '/objects[/{option:page|order|category}/{value:\d+}]',
    [ObjectsController::class, 'list']
)->setName('objectslend_objects')->add($authenticate);

//objects list filtering
$app->post(
    '/objects/filter',
    [ObjectsController::class, 'filter']
)->setName('objectslend_filter_objects')->add($authenticate);

$app->get(
    '/object/remove/{id:\d+}',
    [ObjectsController::class, 'confirmDelete']
)->setName('objectslend_remove_object')->add($authenticate);

$app->post(
    '/object/remove[/{id:\d+}]',
    [ObjectsController::class, 'delete']
)->setName('objectslend_doremove_object')->add($authenticate);

//Batch actions on objects list
$app->post(
    '/objects/batch',
    [ObjectsController::class, 'handleBatch']
)->setName('objectslend_batch-objectslist')->add($authenticate);

$app->get(
    '/objects/remove',
    [ObjectsController::class, 'confirmDelete']
)->setName('objectslend_remove_objects')->add($authenticate);

$app->get(
    '/objects/print',
    [PdfController::class, 'printObjects']
)->setName('objectslend_objects_print')->add($authenticate);

$app->get(
    '/object/print/{id:\d+}',
    [PdfController::class, 'printObject']
)->setName('objectslend_object_print')->add($authenticate);

$app->get(
    '/object/show/{id:\d+}',
    [ObjectsController::class, 'show']
)->setName('objectslend_show_object_lend')->add($authenticate);

$app->get(
    '/object/{action:take|return}/{id:\d+}',
    [ObjectsController::class, 'lend']
)->setName('objectslend_object_take')->add($authenticate);

$app->post(
    '/object/take/{id:\d+}',
    [ObjectsController::class, 'doTake']
)->setName('objectslend_object_dotake')->add($authenticate);

$app->post(
    '/object/return/{id:\d+}',
    [ObjectsController::class, 'doReturn']
)->setName('objectslend_object_doreturn')->add($authenticate);
