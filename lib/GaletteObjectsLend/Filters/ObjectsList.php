<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Objects list filters and paginator
 *
 * PHP version 5
 *
 * Copyright © 2017-2023 The Galette Team
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
 * @copyright 2017-2023 The Galette Team
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL License 3.0 or (at your option) any later version
 * @version   SVN: $Id$
 * @link      http://galette.tuxfamily.org
 * @since     2017-02-10
 */

namespace GaletteObjectsLend\Filters;

use Analog\Analog;
use Galette\Core\Pagination;
use GaletteObjectsLend\Repository\Objects;
use Laminas\Db\Sql\Select;

/**
 * Objects list filters and paginator
 *
 * @name      ObjectsList
 * @category  Filters
 * @package   GaletteObjectsLend
 *
 * @author    Johan Cwiklinski <johan@x-tnd.be>
 * @copyright 2017-2023 The Galette Team
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL License 3.0 or (at your option) any later version
 * @link      http://galette.tuxfamily.org
 *
 * @property string $filter_str
 * @property int $category_filter
 * @property int $active_filter
 * @property int $field_filter
 * @property int $selected
 * @property string $query
 */

class ObjectsList extends Pagination
{
    //filters
    private $filter_str;
    private $category_filter;
    private $active_filter;
    private $field_filter;
    private $selected;

    protected $query;

    protected $objectslist_fields = array(
        'filter_str',
        'category_filter',
        'active_filter',
        'field_filter',
        'selected',
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
        return 'name';
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
        $this->category_filter = null;
        $this->active_filter = null;
        $this->field_filter = null;
        $this->selected = array();
    }

    /**
     * Default isset
     *
     * @param string $name Property name
     *
     * @return object|void
     */
    public function __isset($name)
    {
        if (in_array($name, $this->objectslist_fields)) {
            return true;
        }

        return parent::__isset($name);
    }

    /**
     * Global getter method
     *
     * @param string $name name of the property we want to retrieve
     *
     * @return mixed the called property
     */
    public function __get($name)
    {
        Analog::log(
            '[ObectsList] Getting property `' . $name . '`',
            Analog::DEBUG
        );

        if (in_array($name, $this->pagination_fields)) {
            return parent::__get($name);
        } else {
            if (in_array($name, $this->objectslist_fields)) {
                return $this->$name;
            } else {
                Analog::log(
                    '[ObjectsList] Unable to get property `' . $name . '`',
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
                '[ObjectsList] Setting property `' . $name . '`',
                Analog::DEBUG
            );

            switch ($name) {
                case 'selected':
                    if (is_array($value)) {
                        $this->$name = $value;
                    } elseif ($value !== null) {
                        Analog::log(
                            '[ObjectsList] Value for property `' . $name .
                            '` should be an array (' . gettype($value) . ' given)',
                            Analog::WARNING
                        );
                    }
                    break;
                case 'filter_str':
                    $this->$name = $value;
                    break;
                case 'category_filter':
                    if (is_numeric($value)) {
                        $this->$name = $value;
                    } elseif ($value !== null) {
                        Analog::log(
                            '[ObjectsList] Value for property `' . $name .
                            '` should be an integer (' . gettype($value) . ' given)',
                            Analog::WARNING
                        );
                    } else {
                        $this->$name = null;
                    }
                    break;
                case 'active_filter':
                    switch ($value) {
                        case Objects::ALL_OBJECTS:
                        case Objects::ACTIVE_OBJECTS:
                        case Objects::INACTIVE_OBJECTS:
                            $this->active_filter = $value;
                            break;
                        default:
                            Analog::log(
                                '[ObjectsList] Value for active filter should be either ' .
                                Objects::ACTIVE_OBJECTS . ', ' . Objects::ACTIVE_OBJECTS . ' or ' .
                                Objects::INACTIVE_OBJECTS . ' (' . $value . ' given)',
                                Analog::WARNING
                            );
                            break;
                    }
                    break;
                case 'field_filter':
                    if (is_numeric($value)) {
                        $this->$name = $value;
                    } elseif ($value !== null) {
                        Analog::log(
                            '[ObjectsList] Value for property `' . $name .
                            '` should be an integer (' . gettype($value) . ' given)',
                            Analog::WARNING
                        );
                    }
                    break;
                case 'query':
                    $this->$name = $value;
                    break;
                default:
                    Analog::log(
                        '[ObjectsList] Unable to set proprety `' . $name . '`',
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
     * @return void
     */
    public function setLimit($select)
    {
        $this->setLimits($select);
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

    /**
     * Set commons filters for templates
     *
     * @param \GaletteObjectsLend\Entity\Preferences $prefs Preferences instance
     * @param mixed                                  $view  Template reference
     *
     * @return void
     */
    public function setViewCommonsFilters($prefs, $view)
    {
        $prefs = $prefs->getPreferences();

        $options = [
            Objects::FILTER_NAME    => ($prefs['VIEW_DESCRIPTION'] ?
                _T("Name/description", "objectslend") : _T("Name", "objectslend")),
            Objects::FILTER_SERIAL  => _T("Serial number", "objectslend"),
            Objects::FILTER_ID      => _T("Id", "objectslend")
        ];

        if ($prefs['VIEW_DIMENSION']) {
            $options[Objects::FILTER_DIM] = _T("Dimensions", "objectslend");
        }

        $view->getEnvironment()->addGlobal('field_filter_options', $options);
    }
}
