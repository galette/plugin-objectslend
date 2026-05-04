<?php

/**
 * Copyright © 2003-2026 The Galette Team
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

use Galette\Middleware\Authenticate;
use GaletteObjectsLend\Repository\Categories;
use GaletteObjectsLend\Repository\Objects;
use GaletteObjectsLend\Repository\Status;
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
)->setName('objectslend_preferences')->add(Authenticate::class);

$app->post(
    '/preferences',
    [MainController::class, 'storePreferences']
)->setName('store_objectlend_preferences')->add(Authenticate::class);

$app->get(
    '/category/add',
    [CategoriesController::class, 'add']
)->setName('objectslend_category_add')->add(Authenticate::class);

$app->get(
    '/category/edit/{id:\d+}',
    [CategoriesController::class, 'edit']
)->setName('objectslend_category_edit')->add(Authenticate::class);

$app->post(
    '/category/add',
    [CategoriesController::class, 'doAdd']
)->setName('objectslend_category_action_add')->add(Authenticate::class);

$app->post(
    '/category/edit/{id:\d+}',
    [CategoriesController::class, 'doEdit']
)->setName('objectslend_category_action_edit')->add(Authenticate::class);

$app->get(
    '/{type:category|object}/{mode:photo|thumbnail}[/{id:\d+}]',
    [ImagesController::class, 'lendPicture']
)->setName('objectslend_photo');

$app->get(
    '/categories[/{option:page|order}/{value:\d+}]',
    [CategoriesController::class, 'list']
)->setName('objectslend_categories')->add(Authenticate::class);

//categories list filtering
$app->post(
    '/categories/filter',
    [CategoriesController::class, 'filter']
)->setName('objectslend_filter_categories')->add(Authenticate::class);

$app->get(
    '/category/remove/{id:\d+}',
    [CategoriesController::class, 'confirmDelete']
)->setName('objectslend_remove_category')->add(Authenticate::class);

$app->post(
    '/category/remove/{id:\d+}',
    [CategoriesController::class, 'delete']
)->setName('objectslend_doremove_category')->add(Authenticate::class);

$app->get(
    '/status/add',
    [StatusController::class, 'add']
)->setName('objectslend_status_add')->add(Authenticate::class);

$app->get(
    '/status/edit/{id:\d+}',
    [StatusController::class, 'edit']
)->setName('objectslend_status_edit')->add(Authenticate::class);

$app->post(
    '/status/add',
    [StatusController::class, 'doAdd']
)->setName('objectslend_status_action_add')->add(Authenticate::class);

$app->post(
    '/status/edit/{id:\d+}',
    [StatusController::class, 'doEdit']
)->setName('objectslend_status_action_edit')->add(Authenticate::class);

$app->get(
    '/statuses[/{option:page|order}/{value:\d+}]',
    [StatusController::class, 'list']
)->setName('objectslend_statuses')->add(Authenticate::class);

//status list filtering
$app->post(
    '/statuses/filter',
    [StatusController::class, 'filter']
)->setName('objectslend_filter_statuses')->add(Authenticate::class);

$app->get(
    '/status/remove/{id:\d+}',
    [StatusController::class, 'confirmDelete']
)->setName('objectslend_remove_status')->add(Authenticate::class);

$app->post(
    '/status/remove/{id:\d+}',
    [StatusController::class, 'delete']
)->setName('objectslend_doremove_status')->add(Authenticate::class);

$app->get(
    '/object/add',
    [ObjectsController::class, 'add']
)->setName('objectslend_object_add')->add(Authenticate::class);

$app->get(
    '/object/edit/{id:\d+}',
    [ObjectsController::class, 'edit']
)->setName('objectslend_object_edit')->add(Authenticate::class);

$app->post(
    '/object/{id:\d+}/updatestatus',
    [ObjectsController::class, 'doUpdateStatus']
)->setName('objectslend_object_updatestatus')->add(Authenticate::class);


$app->get(
    '/object/clone/{id:\d+}',
    [ObjectsController::class, 'doClone']
)->setName('objectslend_object_clone')->add(Authenticate::class);

$app->post(
    '/object/add',
    [ObjectsController::class, 'doAdd']
)->setName('objectslend_object_action_add')->add(Authenticate::class);

$app->post(
    '/object/edit/{id:\d+}',
    [ObjectsController::class, 'doEdit']
)->setName('objectslend_object_action_edit')->add(Authenticate::class);

$app->get(
    '/objects[/{option:page|order|category}/{value:\d+}]',
    [ObjectsController::class, 'list']
)->setName('objectslend_objects')->add(Authenticate::class);

//objects list filtering
$app->post(
    '/objects/filter',
    [ObjectsController::class, 'filter']
)->setName('objectslend_filter_objects')->add(Authenticate::class);

$app->get(
    '/object/remove/{id:\d+}',
    [ObjectsController::class, 'confirmDelete']
)->setName('objectslend_remove_object')->add(Authenticate::class);

$app->post(
    '/object/remove[/{id:\d+}]',
    [ObjectsController::class, 'delete']
)->setName('objectslend_doremove_object')->add(Authenticate::class);

//Batch actions on objects list
$app->post(
    '/objects/batch',
    [ObjectsController::class, 'handleBatch']
)->setName('objectslend_batch-objectslist')->add(Authenticate::class);

$app->get(
    '/objects/remove',
    [ObjectsController::class, 'confirmDelete']
)->setName('objectslend_remove_objects')->add(Authenticate::class);

$app->get(
    '/objects/print',
    [PdfController::class, 'printObjects']
)->setName('objectslend_objects_print')->add(Authenticate::class);

$app->get(
    '/object/print/{id:\d+}',
    [PdfController::class, 'printObject']
)->setName('objectslend_object_print')->add(Authenticate::class);

$app->get(
    '/object/show/{id:\d+}',
    [ObjectsController::class, 'show']
)->setName('objectslend_show_object_lend')->add(Authenticate::class);

$app->get(
    '/object/{action:take|return}/{id:\d+}',
    [ObjectsController::class, 'lend']
)->setName('objectslend_object_take')->add(Authenticate::class);

$app->post(
    '/object/take/{id:\d+}',
    [ObjectsController::class, 'doTake']
)->setName('objectslend_object_dotake')->add(Authenticate::class);

$app->post(
    '/object/return/{id:\d+}',
    [ObjectsController::class, 'doReturn']
)->setName('objectslend_object_doreturn')->add(Authenticate::class);
