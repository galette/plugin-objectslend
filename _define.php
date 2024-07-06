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

$this->register(
    'Galette Objects Lend',             //Name
    'Manage rent/lend of object',       //Short description
    'Mélissa Djebel, Johan Cwiklinski', //Author
    '2.1.1',                            //Version
    '1.1.0',                            //Galette version compatibility
    'objectslend',                      //routing name and translation domain
    '2024-07-06',                       //Date
    [
        'objectslend_preferences'       => 'admin',
        'store_objectlend_preferences'  => 'admin',
        'objectslend_category_add'      => 'staff',
        'objectslend_category_edit'     => 'staff',
        'objectslend_category_action_add' => 'staff',
        'objectslend_category_action_edit' => 'staff',
        'objectslend_categories'        => 'staff',
        'objectslend_filter_categories' => 'staff',
        'objectslend_remove_category'   => 'admin',
        'objectslend_doremove_category' => 'admin',
        'objectslend_status_add'        => 'staff',
        'objectslend_status_edit'       => 'staff',
        'objectslend_status_action_add' => 'staff',
        'objectslend_status_action_edit' => 'staff',
        'objectslend_statuses'          => 'staff',
        'objectslend_filter_statuses'   => 'staff',
        'objectslend_remove_status'     => 'admin',
        'objectslend_doremove_status'   => 'admin',
        'objectslend_object_add'        => 'staff',
        'objectslend_object_edit'       => 'staff',
        'objectslend_object_updatestatus' => 'staff',
        'objectslend_object_action_add' => 'staff',
        'objectslend_object_action_edit' => 'staff',
        'objectslend_object_clone'      => 'staff',
        'objectslend_objects'           => 'member',
        'objectslend_filter_objects'    => 'member',
        'objectslend_remove_object'     => 'admin',
        'objectslend_doremove_object'   => 'admin',
        'objectslend_batch-objectslist' => 'staff',
        'objectslend_remove_objects'    => 'admin',
        'objectslend_objects_print'     => 'staff',
        'objectslend_object_print'      => 'staff',
        'objectslend_show_object_lend'  => 'staff',
        'objectslend_object_take'       => 'member',
        'objectslend_object_dotake'     => 'member',
        'objectslend_object_doreturn'   => 'member'
    ]
);
