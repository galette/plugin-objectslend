<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Status list
 *
 * PHP version 5
 *
 * Copyright © 2018 The Galette Team
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
 * @copyright 2018 The Galette Team
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL License 3.0 or (at your option) any later version
 * @link      http://galette.tuxfamily.org
 * @since     2018-01-07
 */

namespace GaletteObjectsLend\Repository;

use Galette\Entity\DynamicFields;

use Analog\Analog;
use Galette\Core\Db;
use Zend\Db\Sql\Expression;
use Galette\Repository\Repository;
use GaletteObjectsLend\Filters\StatusList;
use GaletteObjectsLend\Entity\Preferences;
use GaletteObjectsLend\Entity\LendObject;
use GaletteObjectsLend\Entity\LendCategory;
use GaletteObjectsLend\Entity\LendRent;
use GaletteObjectsLend\Entity\LendStatus;
use Galette\Core\Login;

/**
 * Status list
 *
 * @name      Status
 * @category  Repository
 * @package   GaletteObjectsLend
 *
 * @author    Johan Cwiklinski <johan@x-tnd.be>
 * @copyright 2018 The Galette Team
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL License 3.0 or (at your option) any later version
 * @link      http://galette.tuxfamily.org
 */
class Status
{
    const TABLE = LendStatus::TABLE;
    const PK = LendStatus::PK;

    const ALL = 0;
    const ACTIVE = 1;
    const INACTIVE = 2;

    const DC_STOCK = 0;
    const IN_STOCK = 1;
    const OUT_STOCK = 2;

    const FILTER_NAME = 0;

    const ORDERBY_ID = 0;
    const ORDERBY_NAME = 1;
    const ORDERBY_ACTIVE = 2;
    const ORDERBY_STOCK = 3;
    const ORDERBY_RENTDAYS = 4;

    private $filters = false;
    private $count = null;
    private $errors = array();

    /**
     * Default constructor
     *
     * @param Db         $zdb     Database instance
     * @param Login      $login   Logged in instance
     * @param StatusList $filters Filtering
     */
    public function __construct(Db $zdb, Login $login, StatusList $filters = null)
    {
        $this->zdb = $zdb;
        $this->login = $login;

        if ($filters === null) {
            $this->filters = new StatusList();
        } else {
            $this->filters = $filters;
        }
    }


    /**
     * Get status list
     *
     * @param boolean $as_stt return the results as an array of
     *                        Status object.
     * @param array   $fields field(s) name(s) to get. Should be a string or
     *                        an array. If null, all fields will be
     *                        returned
     * @param boolean $count  true if we want to count members
     * @param boolean $limit  true if we want records pagination
     *
     * @return LendStatus[]|ResultSet
     */
    public function getStatusList(
        $as_stt = false,
        $fields = null,
        $count = true,
        $limit = true
    ) {
        try {
            $select = $this->buildSelect($fields, false, $count);

            //add limits to retrieve only relevant rows
            if ($limit === true) {
                $this->filters->setLimits($select);
            }

            $rows = $this->zdb->execute($select);
            $this->filters->query = $this->zdb->query_string;

            $status = array();
            if ($as_stt) {
                foreach ($rows as $row) {
                    $status[] = new LendStatus($this->zdb, $row);
                }
            } else {
                $status = $rows;
            }
            return $status;
        } catch (\Exception $e) {
            Analog::log(
                'Cannot list categories | ' . $e->getMessage(),
                Analog::WARNING
            );
        }
    }

    /**
     * Get status list
     *
     * @param boolean $as_stt return the results as an array of
     *                        Status object.
     * @param array   $fields field(s) name(s) to get. Should be a string or
     *                        an array. If null, all fields will be
     *                        returned
     *
     * @return LendStatus[]|ResultSet
     */
    public function getList($as_stt = false, $fields = null)
    {
        return $this->getStatusList(
            $as_stt,
            $fields,
            false,
            false
        );
    }

    /**
     * Builds the SELECT statement
     *
     * @param array $fields fields list to retrieve
     * @param bool  $photos true if we want to get only members with photos
     *                      Default to false, only relevant for SHOW_PUBLIC_LIST
     * @param bool  $count  true if we want to count members, defaults to false
     *
     * @return Select SELECT statement
     */
    private function buildSelect($fields, $photos, $count = false)
    {
        try {
            $fieldsList = ( $fields != null )
                            ? (( !is_array($fields) || count($fields) < 1 ) ? (array)'*'
                            : $fields) : (array)'*';

            $select = $this->zdb->select(LEND_PREFIX . self::TABLE, 'c');
            $select->columns($fieldsList);

            if ($this->filters !== false) {
                $this->buildWhereClause($select);
            }
            $select->order($this->buildOrderClause($fields));

            if ($count) {
                $this->proceedCount($select);
            }

            return $select;
        } catch (\Exception $e) {
            Analog::log(
                'Cannot build SELECT clause for objectslend status | ' . $e->getMessage(),
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

            $results = $this->zdb->execute($countSelect);

            $this->count = $results->current()->count;
            if (isset($this->filters) && $this->count > 0) {
                $this->filters->setCounter($this->count);
            }
        } catch (\Exception $e) {
            Analog::log(
                'Cannot count objectslend status | ' . $e->getMessage(),
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
            case self::ORDERBY_ID:
                if ($this->canOrderBy('status_id', $fields)) {
                    $order[] = 'status_id ' . $this->filters->getDirection();
                }
                break;
            case self::ORDERBY_NAME:
                if ($this->canOrderBy('status_text', $fields)) {
                    $order[] = 'status_text ' . $this->filters->getDirection();
                }
                break;
            case self::ORDERBY_ACTIVE:
                if ($this->canOrderBy('is_active', $fields)) {
                    $order[] = 'is_active ' . $this->filters->getDirection();
                }
                break;
            case self::ORDERBY_STOCK:
                if ($this->canOrderBy('is_home_location', $fields)) {
                    $order[] = 'is_home_location ' . $this->filters->getDirection();
                }
                break;
            case self::ORDERBY_RENTDAYS:
                if ($this->canOrderBy('rent_day_number', $fields)) {
                    $order[] = 'rent_day_number ' . $this->filters->getDirection();
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
            if ($this->filters->active_filter == self::ACTIVE) {
                $select->where('c.is_active = true');
            }
            if ($this->filters->active_filter == self::INACTIVE) {
                $select->where('c.is_active = false');
            }

            if ($this->filters->stock_filter == self::IN_STOCK) {
                $select->where('c.is_home_location = true');
            }
            if ($this->filters->stock_filter == self::OUT_STOCK) {
                $select->where('c.is_home_location = false');
            }

            if ($this->filters->filter_str != '') {
                $token = $this->zdb->platform->quoteValue(
                    '%' . strtolower($this->filters->filter_str) . '%'
                );

                $select->where(
                    'c.status_text LIKE ' . $token
                );
            }
        } catch (\Exception $e) {
            Analog::log(
                __METHOD__ . ' | ' . $e->getMessage(),
                Analog::WARNING
            );
        }
    }

    /**
     * Is field allowed to order? it should be present in
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
