<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Categories list filters and paginator
 *
 * PHP version 5
 *
 * Copyright © 2017 The Galette Team
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
 * @category  Filters
 * @package   Galette
 *
 * @author    Johan Cwiklinski <johan@x-tnd.be>
 * @copyright 2017 The Galette Team
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL License 3.0 or (at your option) any later version
 * @version   SVN: $Id$
 * @link      http://galette.tuxfamily.org
 * @since     2017-02-10
 */

namespace GaletteObjectsLend\Filters;

use Analog\Analog;
use Galette\Core\Pagination;
use GaletteObjectsLend\Repository\Categories;

/**
 * Categories list filters and paginator
 *
 * @name      CategoriesList
 * @category  Filters
 * @package   GaletteObjectsLend
 *
 * @author    Johan Cwiklinski <johan@x-tnd.be>
 * @copyright 2017 The Galette Team
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL License 3.0 or (at your option) any later version
 * @link      http://galette.tuxfamily.org
 */

class CategoriesList extends Pagination
{
    //filters
    private $filter_str;
    private $active_filter;

    protected $query;

    protected $categorylist_fields = array(
        'filter_str',
        'active_filter',
        'query'
    );

    /**
     * Default constructor
     */
    public function __construct()
    {
        $this->reinit();
    }

    /**
     * Returns the field we want to default set order to
     *
     * @return string field name
     */
    protected function getDefaultOrder()
    {
        return 'name ';
    }

    /**
     * Reinit default parameters
     *
     * @return void
     */
    public function reinit()
    {

        parent::reinit();
        $this->filter_str = null;
        $this->active_filter = null;
    }

    /**
     * Global getter method
     *
     * @param string $name name of the property we want to retrive
     *
     * @return object the called property
     */
    public function __get($name)
    {

        Analog::log(
            '[CategoriesList] Getting property `' . $name . '`',
            Analog::DEBUG
        );

        if (in_array($name, $this->pagination_fields)) {
            return parent::__get($name);
        } else {
            if (in_array($name, $this->categorylist_fields)) {
                return $this->$name;
            } else {
                Analog::log(
                    '[CategoriesList] Unable to get proprety `' .$name . '`',
                    Analog::WARNING
                );
            }
        }
    }

    /**
     * Global setter method
     *
     * @param string $name  name of the property we want to assign a value to
     * @param object $value a relevant value for the property
     *
     * @return void
     */
    public function __set($name, $value)
    {

        if (in_array($name, $this->pagination_fields)) {
            parent::__set($name, $value);
        } else {
            Analog::log(
                '[CategoriesList] Setting property `' . $name . '`',
                Analog::DEBUG
            );

            switch ($name) {
                case 'filter_str':
                    $this->$name = $value;
                    break;
                case 'active_filter':
                    switch ($value) {
                        case Categories::ALL_CATEGORIES:
                        case Categories::ACTIVE_CATEGORIES:
                        case Categories::INACTIVE_CATEGORIES:
                            $this->active_filter = $value;
                            break;
                        default:
                            Analog::log(
                                '[CategoriesList] Value for active filter should be either ' .
                                CategoriesLend::ACTIVE . ' or ' .
                                CategoriesLend::INACTIVE . ' (' . $value . ' given)',
                                Analog::WARNING
                            );
                            break;
                    }
                    break;
                case 'query':
                    $this->$name = $value;
                    break;
                default:
                    Analog::log(
                        '[CategoriesList] Unable to set proprety `' . $name . '`',
                        Analog::WARNING
                    );
                    break;
            }
        }
    }

    /**
     * Add SQL limit
     *
     * @param Select $select Original select
     *
     * @return <type>
     */
    public function setLimit($select)
    {
        return $this->setLimits($select);
    }

    /**
     * Set counter
     *
     * @param int $c Count
     *
     * @return void
     */
    public function setCounter($c)
    {
        $this->counter = (int)$c;
        $this->countPages();
    }
}
