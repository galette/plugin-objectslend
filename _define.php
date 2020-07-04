<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Configuration file for ObjectsLend plugin
 *
 * PHP version 5
 *
 * Copyright © 2013-2016 Mélissa Djebel
 * Copyright © 2017-2020 The Galette Team
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
 * @copyright 2017-2020 The Galette Team
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL License 3.0 or (at your option) any later version
 * @link      http://galette.tuxfamily.org
 */

$this->register(
    'Galette Objects Lend',             //Name
    'Manage rent/lend of object',       //Short description
    'Mélissa Djebel, Johan Cwiklinski', //Author
    '1.0.0',                            //Version
    '0.9.4',                            //Galette version compatibility
    'objectslend',                      //routing name and translation domain
    '2020-07-04',                       //Date
    [
        'objectslend_preferences'       => 'admin',
        'store_objectlend_preferences'  => 'admin',
        'objectslend_adminimages'       => 'staff',
        'objectslend_adminimages_action' => 'staff',
        'objectslend_category'          => 'staff',
        'objectslend_category_action'   => 'staff',
        'objectslend_categories'        => 'staff',
        'objectslend_filter_categories' => 'staff',
        'objectslend_remove_category'   => 'admin',
        'objectslend_doremove_category' => 'admin',
        'objectslend_status'            => 'staff',
        'objectslend_status_action'     => 'staff',
        'objectslend_statuses'          => 'staff',
        'objectslend_filter_statuses'   => 'staff',
        'objectslend_remove_status'     => 'admin',
        'objectslend_doremove_status'   => 'admin',
        'objectslend_object'            => 'staff',
        'objectslend_object_action'     => 'staff',
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
        'objectslend_object_doreturn'   => 'staff'
    ]
);
