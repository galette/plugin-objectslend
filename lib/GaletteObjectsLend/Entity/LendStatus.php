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

namespace GaletteObjectsLend\Entity;

use Analog\Analog;
use ArrayObject;
use Galette\Core\Db;

/**
 * Lend status management
 *
 * @author Mélissa Djebel <melissa.djebel@gmx.net>
 * @author Johan Cwiklinski <johan@x-tnd.be>
 *
 * @property integer $status_id
 * @property string $status_text
 * @property boolean $in_stock
 * @property boolean $is_active
 * @property integer $rent_day_number
 */
class LendStatus
{
    public const TABLE = 'status';
    public const PK = 'status_id';

    private Db $zdb;

    /** @var array<string,string> */
    private array $fields = array(
        'status_id' => 'integer',
        'status_text' => 'varchar(100)',
        'in_stock' => 'boolean',
        'is_active' => 'boolean',
        'rent_day_number' => 'int'
    );
    private int $status_id;
    private string $status_text = '';
    private bool $in_stock = false;
    private bool $is_active = true;
    private ?int $rent_day_number = null;

    /**
     * Status constructor
     *
     * @param Db                                      $zdb  Database instance
     * @param int|ArrayObject<string,int|string>|null $args Can be null, an ID or a database row
     */
    public function __construct(Db $zdb, int|ArrayObject|null $args = null)
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
     * @param ArrayObject<string,int|string> $r the resultset row
     *
     * @return void
     */
    private function loadFromRS(ArrayObject $r): void
    {
        $this->status_id = (int)$r->status_id;
        $this->status_text = $r->status_text;
        $this->in_stock = $r->in_stock == '1';
        $this->is_active = $r->is_active == '1';
        $this->rent_day_number = $r->rent_day_number;
    }

    /**
     * Store current element
     *
     * @return bool
     */
    public function store(): bool
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
                    $values[$k] = $this->$k ?? null;
                }
            }

            if (!isset($this->status_id) || $this->status_id == '') {
                unset($values[self::PK]);
                $insert = $this->zdb->insert(LEND_PREFIX . self::TABLE)
                        ->values($values);
                $result = $this->zdb->execute($insert);
                if ($result->count() > 0) {
                    if ($this->zdb->isPostgres()) {
                        /** @phpstan-ignore-next-line */
                        $this->status_id = (int)$this->zdb->driver->getLastGeneratedValue(
                            PREFIX_DB . 'lend_status_id_seq'
                        );
                    } else {
                        $this->status_id = (int)$this->zdb->driver->getLastGeneratedValue();
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
     * Get all active statuses sorted by name
     *
     * @param Db $zdb Database instance
     *
     * @return LendStatus[]
     */
    public static function getActiveStatuses(Db $zdb): array
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
            throw $e;
        }
    }

    /**
     * Get all borrowed active statuses sorted by
     *
     * @param Db $zdb Database instance
     *
     * @return LendStatus[]
     */
    public static function getActiveTakeAwayStatuses(Db $zdb): array
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
            throw $e;
        }
    }

    /**
     * Return list of active in stock statuses
     *
     * @param Db $zdb Database instance
     *
     * @return LendStatus[]
     */
    public static function getActiveStockStatuses(Db $zdb): array
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
    public function delete(): bool
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
     * @param string $name name of the property we want to retrieve
     *
     * @return mixed the called property
     */
    public function __get(string $name): mixed
    {
        return $this->$name ?? null;
    }

    /**
     * Global setter method
     *
     * @param string $name  name of the property we want to assign a value to
     * @param mixed  $value a relevant value for the property
     *
     * @return void
     */
    public function __set(string $name, mixed $value): void
    {
        $this->$name = $value;
    }

    /**
     * Generic isset function
     *
     * @param string $name Property name
     *
     * @return bool
     */
    public function __isset(string $name): bool
    {
        return property_exists($this, $name);
    }
}
