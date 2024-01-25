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

namespace GaletteObjectsLend\Repository;

use ArrayObject;
use Galette\Entity\DynamicFields;
use Analog\Analog;
use Galette\Core\Db;
use Laminas\Db\ResultSet\ResultSet;
use Laminas\Db\Sql\Expression;
use Laminas\Db\Sql\Predicate\Operator;
use GaletteObjectsLend\Filters\CategoriesList;
use GaletteObjectsLend\Filters\ObjectsList;
use GaletteObjectsLend\Entity\LendCategory;
use GaletteObjectsLend\Entity\LendObject;
use Galette\Core\Login;
use Galette\Core\Plugins;
use Laminas\Db\Sql\Select;

/**
 * Categories list
 *
 * @author Johan Cwiklinski <johan@x-tnd.be>
 */
class Categories
{
    public const TABLE = LendCategory::TABLE;
    public const PK = LendCategory::PK;

    public const ALL_CATEGORIES = 0;
    public const ACTIVE_CATEGORIES = 1;
    public const INACTIVE_CATEGORIES = 2;

    public const FILTER_NAME = 0;

    public const ORDERBY_NAME = 0;

    private CategoriesList $filters;
    private ?int $count = null;
    /** @var array<string> */
    private array $errors = array();

    private Db $zdb;
    private Plugins $plugins;
    private Login $login;

    /**
     * Default constructor
     *
     * @param Db              $zdb     Database instance
     * @param Login           $login   Logged in instance
     * @param Plugins         $plugins Plugins instance
     * @param ?CategoriesList $filters Filtering
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
     * Get categories list
     *
     * @param boolean       $as_cat return the results as an array of
     *                              Categories object.
     * @param array<string> $fields field(s) name(s) to get. Should be a string or
     *                              an array. If null, all fields will be
     *                              returned
     * @param boolean       $count  true if we want to count members
     * @param boolean       $limit  true if we want records pagination
     *
     * @return LendCategory[]|ResultSet
     */
    public function getCategoriesList(
        bool $as_cat = false,
        array $fields = null,
        bool $count = true,
        bool $limit = true
    ): array|ResultSet {
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
            throw $e;
        }
    }

    /**
     * Get Categories list
     *
     * @param boolean $as_cat return the results as an array of
     *                        Category object.
     * @param ?array  $fields field(s) name(s) to get. If null, all fields will be returned
     *
     * @return LendCategory[]|ResultSet
     */
    public function getList(bool $as_cat = false, array $fields = null): array|ResultSet
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
     * @param ?array<string> $fields fields list to retrieve
     * @param bool           $count  true if we want to count members, defaults to false
     *
     * @return Select SELECT statement
     */
    private function buildSelect(?array $fields, bool $count = false): Select
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
            throw $e;
        }
    }

    /**
     * Count categories from the query
     *
     * @param Select $select Original select
     *
     * @return void
     */
    private function proceedCount(Select $select): void
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
            throw $e;
        }
    }

    /**
     * Builds the order clause
     *
     * @param ?string[] $fields Fields list to ensure ORDER clause
     *                          references selected fields. Optional.
     *
     * @return array<string> SQL ORDER clauses
     */
    private function buildOrderClause(array $fields = null): array
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
     * @return void
     */
    private function buildWhereClause(Select $select): void
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
                $select->having(
                    new Operator(
                        new Expression('COUNT(o.' . self::PK . ')'),
                        '>',
                        '0'
                    )
                );
            }
        } catch (\Exception $e) {
            Analog::log(
                __METHOD__ . ' | ' . $e->getMessage(),
                Analog::WARNING
            );
            throw $e;
        }
    }

    /**
     * Is field allowed to order? it should be present in
     * provided fields list (those that are SELECT'ed).
     *
     * @param string        $field_name Field name to order by
     * @param array<string> $fields     SELECTE'ed fields
     *
     * @return bool
     */
    private function canOrderBy(string $field_name, array $fields): bool
    {
        if (!is_array($fields)) {
            return true;
        } elseif (in_array($field_name, $fields)) {
            return true;
        } else {
            Analog::log(
                'Trying to order by ' . $field_name . ' while it is not in ' .
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
    public function getCount(): int
    {
        return $this->count;
    }

    /**
     * Get registered errors
     *
     * @return array<string>
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
