<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Categories list
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
 * @category  Repository
 * @package   Galette
 *
 * @author    Johan Cwiklinski <johan@x-tnd.be>
 * @copyright 2017 The Galette Team
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL License 3.0 or (at your option) any later version
 * @link      http://galette.tuxfamily.org
 * @since     2017-02-10
 */

namespace GaletteObjectsLend\Repository;

use Galette\Entity\DynamicFields;

use Analog\Analog;
use Galette\Core\Db;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Predicate;
use Galette\Repository\Repository;
use GaletteObjectsLend\Filters\CategoriesList;
use GaletteObjectsLend\Preferences;
use GaletteObjectsLend\LendObject;
use GaletteObjectsLend\LendCategory;
use GaletteObjectsLend\LendRent;
use GaletteObjectsLend\LendStatus;
use Galette\Core\Login;
use Galette\Core\Plugins;

/**
 * Categories list
 *
 * @name Categories
 * @category  Repository
 * @package   GaletteObjectsLend
 *
 * @author    Johan Cwiklinski <johan@x-tnd.be>
 * @copyright 2017 The Galette Team
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL License 3.0 or (at your option) any later version
 * @link      http://galette.tuxfamily.org
 */
class Categories
{
    const TABLE = LendCategory::TABLE;
    const PK = LendCategory::PK;

    const ALL_CATEGORIES = 0;
    const ACTIVE_CATEGORIES = 1;
    const INACTIVE_CATEGORIES = 2;

    const FILTER_NAME = 0;

    const ORDERBY_NAME = 0;

    private $filters = false;
    private $count = null;
    private $errors = array();
    private $plugins;

    /**
     * Default constructor
     *
     * @param Db             $zdb     Database instance
     * @param Login          $login   Logged in instance
     * @param Plugins        $plugins Plugins instance
     * @param CategoriesList $filters Filtering
     */
    public function __construct(Db $zdb, Login $login, Plugins $plugins, CategoriesList $filters = null)
    {
        $this->zdb = $zdb;
        $this->login = $login;
        $this->plugins = $plugins;

        if ($filters === null) {
            $this->filters = new CategoriesList();
        } else {
            $this->filters = $filters;
        }
    }


    /**
     * Get members list
     *
     * @param boolean $as_cat return the results as an array of
     *                        Categories object.
     * @param array   $fields field(s) name(s) to get. Should be a string or
     *                        an array. If null, all fields will be
     *                        returned
     * @param boolean $count  true if we want to count members
     * @param boolean $limit  true if we want records pagination
     *
     * @return LendObject[]|ResultSet
     */
    public function getCategoriesList(
        $as_cat = false,
        $fields = null,
        $count = true,
        $limit = true
    ) {
        try {
            $select = $this->buildSelect($fields, $count);

            //add limits to retrieve only relevant rows
            if ($limit === true) {
                $this->filters->setLimit($select);
            }

            $rows = $this->zdb->execute($select);
            $this->filters->query = $this->zdb->query_string;

            $categories = array();
            if ($as_cat) {
                foreach ($rows as $row) {
                    $categories[] = new LendCategory($this->zdb, $this->plugins, $row);
                }
            } else {
                $categories = $rows;
            }
            return $categories;
        } catch (\Exception $e) {
            Analog::log(
                'Cannot list categories | ' . $e->getMessage(),
                Analog::WARNING
            );
        }
    }

    /**
     * Get Categories list
     *
     * @param boolean $as_cat return the results as an array of
     *                        Category object.
     * @param array   $fields field(s) name(s) to get. Should be a string or
     *                        an array. If null, all fields will be
     *                        returned
     *
     * @return LendCategory[]|ResultSet
     */
    public function getList($as_cat = false, $fields = null)
    {
        return $this->getCategoriesList(
            $as_cat,
            $fields,
            false,
            false
        );
    }

    /**
     * Builds the SELECT statement
     *
     * @param array $fields fields list to retrieve
     * @param bool  $count  true if we want to count members, defaults to false
     *
     * @return Select SELECT statement
     */
    private function buildSelect($fields, $count = false)
    {
        try {
            $fieldsList = [
                '*',
                'objects_count'     => new Expression('COUNT(o.' . self::PK . ')'),
                'objects_price_sum' => new Expression('SUM(o.price)')
            ];

            if ($fields !== null && is_array($fields)) {
                array_merge($fieldsList, $fields);
            }

            $select = $this->zdb->select(LEND_PREFIX . self::TABLE, 'c');
            $select->columns($fieldsList);

            $select->join(
                array('o' => PREFIX_DB . LEND_PREFIX . LendObject::TABLE),
                'o.' . LendCategory::PK . '=c.' . LendCategory::PK,
                [],
                $select::JOIN_LEFT
            );


            if ($this->filters !== false) {
                $this->buildWhereClause($select);
            }
            $select->order($this->buildOrderClause($fields));

            if ($count) {
                $this->proceedCount($select);
            }

            $select->group(
                'c.category_id'
            );

            return $select;
        } catch (\Exception $e) {
            Analog::log(
                'Cannot build SELECT clause for categories | ' . $e->getMessage(),
                Analog::WARNING
            );
            return false;
        }
    }

    /**
     * Count members from the query
     *
     * @param Select $select Original select
     *
     * @return void
     */
    private function proceedCount($select)
    {
        try {
            $countSelect = clone $select;
            $countSelect->reset($countSelect::COLUMNS);
            $countSelect->reset($countSelect::ORDER);
            $countSelect->reset($countSelect::HAVING);
            $countSelect->reset($countSelect::JOINS);
            $countSelect->columns(
                array(
                    'count' => new Expression('count(c.' . self::PK . ')')
                )
            );

            $have = $select->having;
            if ($have->count() > 0) {
                foreach ($have->getPredicates() as $h) {
                    $countSelect->where($h);
                }
            }

            $joins = $select->getRawState($select::JOINS);
            foreach ($joins as $join) {
                $countSelect->join(
                    $join['name'],
                    $join['on'],
                    [],
                    $join['type']
                );
            }

            $results = $this->zdb->execute($countSelect);

            $this->count = $results->current()->count;
            if (isset($this->filters) && $this->count > 0) {
                $this->filters->setCounter($this->count);
            }
        } catch (\Exception $e) {
            Analog::log(
                'Cannot count categories | ' . $e->getMessage(),
                Analog::WARNING
            );
            return false;
        }
    }

    /**
     * Builds the order clause
     *
     * @param array $fields Fields list to ensure ORDER clause
     *                      references selected fields. Optionnal.
     *
     * @return string SQL ORDER clause
     */
    private function buildOrderClause($fields = null)
    {
        $order = array();
        switch ($this->filters->orderby) {
            case self::ORDERBY_NAME:
                if ($this->canOrderBy('name', $fields)) {
                    $order[] = 'name ' . $this->filters->getDirection();
                }
                break;
        }

        return $order;
    }

    /**
     * Builds where clause, for filtering on simple list mode
     *
     * @param Select $select Original select
     *
     * @return string SQL WHERE clause
     */
    private function buildWhereClause($select)
    {
        try {
            //if there are filters on objects; add them
            if ($this->filters->objects_filters instanceof ObjectsList) {
                $objects = new Objects(
                    $this->zdb,
                    $this->plugins,
                    new \GaletteObjectsLend\Entity\Preferences($this->zdb),
                    $this->filters->objects_filters
                );
                $objects->buildWhereClause($select);
            }

            if ($this->filters->active_filter == self::ACTIVE_CATEGORIES) {
                $select->where('c.is_active = true');
            }
            if ($this->filters->active_filter == self::INACTIVE_CATEGORIES) {
                $select->where('c.is_active = false');
            }

            if ($this->filters->filter_str != '') {
                $token = $this->zdb->platform->quoteValue(
                    '%' . strtolower($this->filters->filter_str) . '%'
                );

                $select->where(
                    'c.name LIKE ' . $token
                );
            }

            if ($this->filters->not_empty == true) {
                $select->having(new Predicate\Operator('objects_count', '>', '0'));
            }
        } catch (\Exception $e) {
            Analog::log(
                __METHOD__ . ' | ' . $e->getMessage(),
                Analog::WARNING
            );
        }
    }

    /**
     * Is field allowed to order? it shoulsd be present in
     * provided fields list (those that are SELECT'ed).
     *
     * @param string $field_name Field name to order by
     * @param array  $fields     SELECTE'ed fields
     *
     * @return boolean
     */
    private function canOrderBy($field_name, $fields)
    {
        if (!is_array($fields)) {
            return true;
        } elseif (in_array($field_name, $fields)) {
            return true;
        } else {
            Analog::log(
                'Trying to order by ' . $field_name  . ' while it is not in ' .
                'selected fields.',
                Analog::WARNING
            );
            return false;
        }
    }

    /**
     * Get count for current query
     *
     * @return int
     */
    public function getCount()
    {
        return $this->count;
    }

    /**
     * Get registered errors
     *
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }
}
