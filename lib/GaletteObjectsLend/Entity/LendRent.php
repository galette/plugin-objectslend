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
use Galette\Entity\Adherent;
use Galette\Repository\Members;

/**
 * Rents
 *
 * @author Mélissa Djebel <melissa.djebel@gmx.net>
 * @author Johan Cwiklinski <johan@x-tnd.be>
 *
 * @property integer $rent_id
 * @property integer $object_id
 * @property ?string $date_begin
 * @property ?string $date_forecast
 * @property ?string $date_end
 * @property integer $status_id
 * @property ?integer $adherent_id
 * @property string $comments
 * @property bool $in_stock
 * @property string $status_text
 * @property string $nom_adh
 * @property string $prenom_adh
 * @property string $pseudo_adh
 * @property string $email_adh
 */
class LendRent
{
    public const TABLE = 'rents';
    public const PK = 'rent_id';

    /** @var array<string, string> */
    private array $fields = array(
        'rent_id' => 'integer',
        'object_id' => 'integer',
        'date_begin' => 'datetime',
        'date_forecast' => 'datetime',
        'date_end' => 'datetime',
        'status_id' => 'integer',
        'adherent_id' => 'integer',
        'comments' => 'varchar(200)'
    );
    private int $rent_id;
    private int $object_id;
    private ?string $date_begin;
    private ?string $date_forecast;
    private ?string $date_end;
    private ?int $status_id;
    private ?int $adherent_id;
    private string $comments = '';
    private bool $in_stock;

    private string $status_text;

    private ?string $nom_adh = '';
    private ?string $prenom_adh = '';
    private ?string $pseudo_adh = '';
    private ?string $email_adh = '';

    /**
     * Default constructor
     *
     * @param int|ArrayObject<string,int|string>|null $args Either an int with rent id, null, or a resultset row
     */
    public function __construct(int|ArrayObject|null $args = null)
    {
        global $zdb;

        $date = new \DateTime();
        $this->date_begin = $date->format('Y-m-d H:i:s');

        if (is_int($args)) {
            try {
                $select = $zdb->select(LEND_PREFIX . self::TABLE)
                        ->where(array(self::PK => $args));
                $result = $zdb->execute($select);
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
        $this->rent_id = (int)$r->rent_id;
        $this->object_id = (int)$r->object_id;
        $this->date_begin = $r->date_begin;
        $this->date_forecast = $r->date_forecast;
        $this->date_end = $r->date_end;
        $this->status_id = (int)$r->status_id;
        if ($r->adherent_id !== null) {
            $this->adherent_id = (int)$r->adherent_id;
        }
        $this->comments = $r->comments;
    }

    /**
     * Store current element
     *
     * @return bool
     */
    public function store(): bool
    {
        global $zdb;

        try {
            $zdb->connection->beginTransaction();
            $values = array();

            foreach ($this->fields as $k => $v) {
                $values[$k] = $this->$k ?? null;
            }

            if (!isset($this->rent_id) || $this->rent_id == '') {
                unset($values[self::PK]);
                $insert = $zdb->insert(LEND_PREFIX . self::TABLE)
                        ->values($values);
                $result = $zdb->execute($insert);
                if ($result->count() > 0) {
                    if ($zdb->isPostgres()) {
                        $this->rent_id = (int)$zdb->driver->getLastGeneratedValue(
                            PREFIX_DB . 'lend_rents_id_seq'
                        );
                    } else {
                        $this->rent_id = (int)$zdb->driver->getLastGeneratedValue();
                    }
                    Analog::log(
                        'Rent #' . $this->rent_id . ' added.',
                        Analog::DEBUG
                    );
                    $update = $zdb->update(LEND_PREFIX . LendObject::TABLE)
                        ->set([self::PK => $this->rent_id])
                        ->where([LendObject::PK => $this->object_id]);
                    $zdb->execute($update);
                    Analog::log(
                        'Rent set for object #' . $this->object_id,
                        Analog::DEBUG
                    );
                } else {
                    throw new \Exception(_T("Rent has not been added", "objectslend"));
                }
            } else {
                $update = $zdb->update(LEND_PREFIX . self::TABLE)
                        ->set($values)
                        ->where(array(self::PK => $this->rent_id));
                $zdb->execute($update);
            }
            $zdb->connection->commit();
            return true;
        } catch (\Exception $e) {
            $zdb->connection->rollBack();
            Analog::log(
                'Something went wrong :\'( | ' . $e->getMessage() . "\n" .
                    $e->getTraceAsString(),
                Analog::ERROR
            );
            return false;
        }
    }

    /**
     * Get rent history for a given object sorted
     *
     * @param integer $object_id Object ID
     * @param boolean $only_last Only retrieve last rent (for list display)
     * @param string  $order     Order clause, defaults to 'date_begin DESC'
     *
     * @return LendRent[]
     */
    public static function getRentsForObjectId(int $object_id, bool $only_last = false, string $order = 'date_begin desc'): array
    {
        global $zdb;

        try {
            $select = $zdb->select(LEND_PREFIX . self::TABLE)
                ->join(
                    PREFIX_DB . Adherent::TABLE,
                    PREFIX_DB . Adherent::TABLE . '.id_adh = ' . PREFIX_DB . LEND_PREFIX . self::TABLE . '.adherent_id',
                    '*',
                    'left'
                )
                ->join(
                    PREFIX_DB . LEND_PREFIX . LendStatus::TABLE,
                    PREFIX_DB . LEND_PREFIX . LendStatus::TABLE . '.status_id = ' . PREFIX_DB .
                        LEND_PREFIX . self::TABLE . '.status_id'
                )
                ->where(array('object_id' => $object_id))
                ->order($order);

            if ($only_last === true) {
                $select->offset(0)->limit(1);
            }

            $rents = array();
            $rows = $zdb->execute($select);

            foreach ($rows as $r) {
                $rt = new LendRent($r);
                $rt->status_text = $r->status_text;
                $rt->status_id = (int)$r->status_id;
                $rt->in_stock = $r->in_stock == '1';
                $rt->prenom_adh = $r->prenom_adh;
                $rt->nom_adh = $r->nom_adh;
                $rt->pseudo_adh = $r->pseudo_adh;
                $rt->email_adh = $r->email_adh;
                $rents[] = $rt;
            }

            return $rents;
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
     * Close all open rents for a given object with given comment
     *
     * @param int    $object_id Object ID
     * @param string $comments  Comment to add on lend that will be closed
     *
     * @return boolean
     */
    public static function closeAllRentsForObject(int $object_id, string $comments): bool
    {
        global $zdb;

        try {
            $select = $zdb->select(LEND_PREFIX . self::TABLE)
                ->where(
                    array(
                        'object_id' => $object_id,
                        'date_end' => null
                    )
                );
            $rows = $zdb->execute($select);

            foreach ($rows as $r) {
                $rent = new LendRent($r);
                $rent->date_end = date('Y-m-d H:i:s');
                $rent->comments = $comments; //FIXME: will replace any existing comments :/
                $rent->store();
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
     * Get active members sorted by name
     *
     * @return Adherent[]
     */
    public static function getAllActivesAdherents(): array
    {
        try {
            $filters = new \Galette\Filters\MembersList();
            $filters->filter_account = Members::ACTIVE_ACCOUNT;
            $members = new Members($filters);
            $adherents = $members->getMembersList(
                true,
                null,
                false,
                false,
                false,
                false
            );

            return $adherents;
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
     * Global getter method
     *
     * @param string $name name of the property we want to retrieve
     *
     * @return mixed the called property
     */
    public function __get(string $name): mixed
    {
        switch ($name) {
            case 'date_begin':
            case 'date_end':
                if (($this->$name ?? '') != '') {
                    $dt = new \DateTime($this->$name);
                    return $dt->format(_T('Y-m-d H:i', 'objectslend'));
                }
                return '';
            case 'date_forecast':
                if (($this->$name ?? '') != '') {
                    $dt = new \DateTime($this->$name);
                    return $dt->format(_T('Y-m-d'));
                }
                return '';
            default:
                return $this->$name ?? null;
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
    public function __set(string $name, mixed $value): void
    {
        switch ($name) {
            case 'adherent_id':
                if ((int)$value > 0) {
                    $this->$name = (int)$value;
                } else {
                    $this->$name = null;
                }
                break;
            case 'status_id':
                if ((int)$value > 0) {
                    $this->$name = (int)$value;
                }
                break;
            case 'date_forecast':
            case 'date_begin':
            case 'date_end':
                $fmt = "Y-m-d";
                $tfmt = __("Y-m-d");
                if ($name == 'date_begin' || $name == 'date_end') {
                    $fmt .= ' H:i:s';
                    $tfmt = __($fmt, 'objectslend');
                }
                try {
                    $d = \DateTime::createFromFormat($tfmt, $value);
                    if ($d === false) {
                        //try with non localized date
                        $d = \DateTime::createFromFormat($fmt, $value);
                        if ($d === false) {
                            throw new \Exception('Incorrect format');
                        }
                        $this->$name = $d->format($fmt);
                    }
                    $this->$name = $d->format($fmt);
                } catch (\Exception $e) {
                    $this->$name = null;
                    Analog::log(
                        sprintf('Invalid %1$s date %2$s, required %3$s or %4$s', $name, $value, $tfmt, $fmt),
                        Analog::WARNING
                    );
                }
                break;
            default:
                $this->$name = $value;
                break;
        }
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
