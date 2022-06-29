<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Public Class LendStatus
 * Store informations about a lend status
 *
 * PHP version 5
 *
 * Copyright © 2013-2016 Mélissa Djebel
 * Copyright © 2017-2021 The Galette Team
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
 * @Copyright 2017-2021 The Galette Team
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL License 3.0 or (at your option) any later version
 * @link      https://galette.eu
 */

namespace GaletteObjectsLend\Entity;

use Analog\Analog;
use Galette\Core\Db;

/**
 * Lend status management
 *
 * @name      LendStatus
 * @category  Entity
 * @package   ObjectsLend
 * @author    Mélissa Djebel <melissa.djebel@gmx.net>
 * @author    Johan Cwiklinski <johan@x-tnd.be>
 * @copyright 2013-2016 Mélissa Djebel
 * @copyright 2017-2021 The Galette Team
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL License 3.0 or (at your option) any later version
 * @link      https://galette.eu
 */
class LendStatus
{
    public const TABLE = 'status';
    public const PK = 'status_id';

    private $zdb;

    private $fields = array(
        'status_id' => 'integer',
        'status_text' => 'varchar(100)',
        'in_stock' => 'boolean',
        'is_active' => 'boolean',
        'rent_day_number' => 'int'
    );
    private $status_id;
    private $status_text = '';
    private $in_stock = false;
    private $is_active = true;
    private $rent_day_number = null;

    /**
     * Status constructor
     *
     * @param Db    $zdb  Database instance
     * @param mixed $args Can be null, an ID or a database row
     */
    public function __construct(Db $zdb, $args = null)
    {
        $this->zdb = $zdb;

        if (is_int($args)) {
            try {
                $select = $this->zdb->select(LEND_PREFIX . self::TABLE)
                        ->where([self::PK => $args]);
                $result = $this->zdb->execute($select);
                if ($result->count() == 1) {
                    $this->loadFromRS($result->current());
                }
            } catch (\Exception $e) {
                Analog::log(
                    'Something went wrong :\'( | ' . $e->getMessage() . "\n" .
                        $e->getTraceAsString(),
                    Analog::ERROR
                );
            }
        } elseif (is_object($args)) {
            $this->loadFromRS($args);
        }
    }

    /**
     * Populate object from a resultset row
     *
     * @param ResultSet $r the resultset row
     *
     * @return void
     */
    private function loadFromRS($r)
    {
        $this->status_id = $r->status_id;
        $this->status_text = $r->status_text;
        $this->in_stock = $r->in_stock == '1' ? true : false;
        $this->is_active = $r->is_active == '1' ? true : false;
        $this->rent_day_number = $r->rent_day_number;
    }

    /**
     * Enregistre l'élément en cours que ce soit en insert ou update
     *
     * @return bool False si l'enregistrement a échoué, true si aucune erreur
     */
    public function store()
    {
        try {
            $values = array();

            foreach ($this->fields as $k => $v) {
                if (
                    ($k === 'is_active' || $k === 'in_stock')
                    && $this->$k === false
                ) {
                    //Handle booleans for postgres ; bugs #18899 and #19354
                    $values[$k] = $this->zdb->isPostgres() ? 'false' : 0;
                } else {
                    $values[$k] = $this->$k;
                }
            }

            if (!isset($this->status_id) || $this->status_id == '') {
                unset($values[self::PK]);
                $insert = $this->zdb->insert(LEND_PREFIX . self::TABLE)
                        ->values($values);
                $result = $this->zdb->execute($insert);
                if ($result->count() > 0) {
                    if ($this->zdb->isPostgres()) {
                        $this->status_id = $this->zdb->driver->getLastGeneratedValue(
                            PREFIX_DB . 'lend_status_id_seq'
                        );
                    } else {
                        $this->status_id = $this->zdb->driver->getLastGeneratedValue();
                    }
                } else {
                    throw new \Exception(_T("Status has not been added :(", "objectslend"));
                }
            } else {
                $update = $this->zdb->update(LEND_PREFIX . self::TABLE)
                        ->set($values)
                        ->where(array(self::PK => $this->status_id));
                $this->zdb->execute($update);
            }
            return true;
        } catch (\Exception $e) {
            Analog::log(
                'Something went wrong :\'( | ' . $e->getMessage() . "\n" .
                    $e->getTraceAsString(),
                Analog::ERROR
            );
            return false;
        }
    }

    /**
     * Renvoi tous les statuts actifs triés par nom
     *
     * @param Db $zdb Database instance
     *
     * @return LendStatus[] La liste des statuts actifs triés
     */
    public static function getActiveStatuses(Db $zdb)
    {
        try {
            $select = $zdb->select(LEND_PREFIX . self::TABLE)
                    ->where(array('is_active' => 1))
                    ->order('status_text');

            $status = array();
            $result = $zdb->execute($select);
            foreach ($result as $r) {
                $status[] = new LendStatus($zdb, $r);
            }
            return $status;
        } catch (\Exception $e) {
            Analog::log(
                'Something went wrong :\'( | ' . $e->getMessage() . "\n" .
                    $e->getTraceAsString(),
                Analog::ERROR
            );
            return false;
        }
    }

    /**
     * Renvoi tous les statuts actifs considéré comme empruntés triés par nom
     *
     * @param Db $zdb Database instance
     *
     * @return LendStatus[] La liste des statuts actifs triés
     */
    public static function getActiveTakeAwayStatuses(Db $zdb)
    {
        try {
            $select = $zdb->select(LEND_PREFIX . self::TABLE)
                    ->where(array('is_active' => 1, 'in_stock' => 0))
                    ->order('status_text');

            $status = array();
            $result = $zdb->execute($select);
            foreach ($result as $r) {
                $status[] = new LendStatus($zdb, $r);
            }
            return $status;
        } catch (\Exception $e) {
            Analog::log(
                'Something went wrong :\'( | ' . $e->getMessage() . "\n" .
                    $e->getTraceAsString(),
                Analog::ERROR
            );
            return false;
        }
    }

    /**
     * Return list of active in stock statuses
     *
     * @param Db $zdb Database instance
     *
     * @return LendStatus[]
     */
    public static function getActiveStockStatuses(Db $zdb)
    {
        try {
            $select = $zdb->select(LEND_PREFIX . self::TABLE)
                    ->where(array('is_active' => 1, 'in_stock' => 1))
                    ->order('status_text');

            $status = array();
            $result = $zdb->execute($select);
            foreach ($result as $r) {
                $status[] = new LendStatus($zdb, $r);
            }
            return $status;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Delete status
     *
     * @return boolean
     */
    public function delete()
    {
        try {
            $delete = $this->zdb->delete(LEND_PREFIX . self::TABLE)
                    ->where(array(self::PK => $this->status_id));
            $this->zdb->execute($delete);
            return true;
        } catch (\Exception $e) {
            Analog::log(
                'Something went wrong :\'( | ' . $e->getMessage() . "\n" .
                    $e->getTraceAsString(),
                Analog::ERROR
            );
            return false;
        }
    }

    /**
     * Global getter method
     *
     * @param string $name name of the property we want to retrive
     *
     * @return false|object the called property
     */
    public function __get($name)
    {
        return $this->$name;
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
        $this->$name = $value;
    }

    /**
     * Generic isset function
     *
     * @param $name Property name
     *
     * @return bool
     */
    public function __isset($name)
    {
        return property_exists($this, $name);
    }
}
