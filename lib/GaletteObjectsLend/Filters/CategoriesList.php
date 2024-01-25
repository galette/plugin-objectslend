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

namespace GaletteObjectsLend\Filters;

use Analog\Analog;
use Galette\Core\Pagination;
use GaletteObjectsLend\Repository\Categories;
use Laminas\Db\Sql\Select;

/**
 * Categories list filters and paginator
 *
 * @author Johan Cwiklinski <johan@x-tnd.be>
 *
 * @property ?string $filter_str
 * @property ?int $active_filter
 * @property ?bool $not_empty
 * @property ?ObjectsList $objects_filters
 * @property string $query
 */

class CategoriesList extends Pagination
{
    //filters
    private $filter_str;
    private $active_filter;
    private $not_empty;
    private $objects_filters;

    protected $query;

    /** @var array<string> */
    protected array $categorylist_fields = array(
        'filter_str',
        'active_filter',
        'not_empty',
        'objects_filters',
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
    protected function getDefaultOrder(): string
    {
        return 'name ';
    }

    /**
     * Reinit default parameters
     *
     * @return void
     */
    public function reinit(): void
    {
        parent::reinit();
        $this->filter_str = null;
        $this->active_filter = null;
        $this->not_empty = null;
        $this->objects_filters = null;
    }

    /**
     * Global getter method
     *
     * @param string $name name of the property we want to retrieve
     *
     * @return mixed the called property
     */
    public function __get(string $name)
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
                    '[CategoriesList] Unable to get property `' . $name . '`',
                    Analog::WARNING
                );
            }
        }
    }

    /**
     * Global setter method
     *
     * @param string $name  name of the property we want to assign a value to
     * @param mixed  $value a relevant value for the property
     *
     * @return void
     */
    public function __set(string $name, $value): void
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
                case 'query':
                case 'not_empty':
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
                                Categories::ALL_CATEGORIES . ', ' . Categories::ACTIVE_CATEGORIES . ' or ' .
                                Categories::INACTIVE_CATEGORIES . ' (' . $value . ' given)',
                                Analog::WARNING
                            );
                            break;
                    }
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
     * @return void
     */
    public function setLimit(Select $select): void
    {
        $this->setLimits($select);
    }

    /**
     * Set objects filter
     *
     * @param ObjectsList $filters Filters for objects list
     *
     * @return self
     */
    public function setObjectsFilter(ObjectsList $filters): self
    {
        $this->objects_filters = $filters;
        return $this;
    }
}
