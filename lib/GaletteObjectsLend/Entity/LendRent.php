<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Public Class LendRent
 * Store all informations about rent status and time of an object
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
 * @Copyright 2017-2020 The Galette Team
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL License 3.0 or (at your option) any later version
 * @link      http://galette.tuxfamily.org
 * @since     Available since 0.7
 */

namespace GaletteObjectsLend\Entity;

use Analog\Analog;
use Galette\Entity\Adherent;
use Galette\Repository\Members;

class LendRent
{

    const TABLE = 'rents';
    const PK = 'rent_id';

    private $fields = array(
        'rent_id' => 'integer',
        'object_id' => 'integer',
        'date_begin' => 'datetime',
        'date_forecast' => 'datetime',
        'date_end' => 'datetime',
        'status_id' => 'integer',
        'adherent_id' => 'integer',
        'comments' => 'varchar(200)'
    );
    private $rent_id;
    private $object_id;
    private $date_begin;
    private $date_forecast;
    private $date_end;
    private $status_id;
    private $adherent_id;
    private $comments = '';
    private $in_stock;
    // Join sur table Status
    private $status_text;
    // Left join sur table adhérents
    private $nom_adh = '';
    private $prenom_adh = '';
    private $pseudo_adh = '';
    private $email_adh = '';

    /**
     * Construit un nouvel historique d'emprunt à partir de la BDD (à partir de son ID) ou vierge
     *
     * @param int|object $args Peut être null, un ID ou une ligne de la BDD
     */
    public function __construct($args = null)
    {
        global $zdb;

        $this->date_begin = date('Y-m-d H:i:s');

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
     * @param ResultSet $r the resultset row
     *
     * @return void
     */
    private function loadFromRS($r)
    {
        $this->rent_id = $r->rent_id;
        $this->object_id = $r->object_id;
        $this->date_begin = $r->date_begin;
        $this->date_forecast = $r->date_forecast;
        $this->date_end = $r->date_end;
        $this->status_id = $r->status_id;
        $this->adherent_id = $r->adherent_id;
        $this->comments = $r->comments;
    }

    /**
     * Enregistre l'élément en cours que ce soit en insert ou update
     *
     * @return bool False si l'enregistrement a échoué, true si aucune erreur
     */
    public function store()
    {
        global $zdb;

        try {
            $zdb->connection->beginTransaction();
            $values = array();

            foreach ($this->fields as $k => $v) {
                $values[$k] = $this->$k;
            }

            if (!isset($this->rent_id) || $this->rent_id == '') {
                unset($values[self::PK]);
                $insert = $zdb->insert(LEND_PREFIX . self::TABLE)
                        ->values($values);
                $result = $zdb->execute($insert);
                if ($result->count() > 0) {
                    if ($zdb->isPostgres()) {
                        $this->rent_id = $zdb->driver->getLastGeneratedValue(
                            PREFIX_DB . 'lend_rents_id_seq'
                        );
                    } else {
                        $this->rent_id = $zdb->driver->getLastGeneratedValue();
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
     * Retourne tous les historiques d'emprunts pour un objet donné trié par date de début
     * les plus récents en 1er.
     *
     * @param integer $object_id ID de l'objet dont on souhaite l'historique d'emprunt
     * @param boolean $only_last Only retrieve last rent (for list display)
     * @param string  $order     Order clause, defaults to 'date_begin DESC'
     *
     * @return LendRent[] Tableau d'objects emprunts
     */
    public static function getRentsForObjectId($object_id, $only_last = false, $order = 'date_begin desc')
    {
        global $zdb;

        try {
            $select = $zdb->select(LEND_PREFIX . self::TABLE)
                    ->join(PREFIX_DB . Adherent::TABLE, PREFIX_DB . Adherent::TABLE . '.id_adh = ' . PREFIX_DB . LEND_PREFIX . self::TABLE . '.adherent_id', '*', 'left')
                    ->join(PREFIX_DB . LEND_PREFIX . LendStatus::TABLE, PREFIX_DB . LEND_PREFIX . LendStatus::TABLE . '.status_id = ' . PREFIX_DB . LEND_PREFIX . self::TABLE . '.status_id')
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
                $rt->status_id = $r->status_id;
                $rt->in_stock = $r->in_stock == '1' ? true : false;
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
            return false;
        }
    }

    /**
     * Ferme tous les emprunts ouverts pour un objet donné avec le commentaire indiqué
     *
     * @param int    $object_id ID de l'objet surlequel fermer les emprunts
     * @param string $comments  Commentaire à mettre sur les emprunts
     *
     * @return boolean True si OK, False si une erreur SQL est survenue
     */
    public static function closeAllRentsForObject($object_id, $comments)
    {
        global $zdb;

        try {
            $select = $zdb->select(LEND_PREFIX . self::TABLE)
                    ->where(array(
                'object_id' => $object_id,
                'date_end' => null
                    ));
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
     * Renvoi une liste de tous les adhérents actifs triés par nom
     *
     * @return \Galette\Entity\Adherent[] Tableau des adhérents actifs triés par nom
     */
    public static function getAllActivesAdherents()
    {
        try {
            $filters = new \Galette\Filters\MembersList();
            $filters->account_status_filter = Members::ACTIVE_ACCOUNT;
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
        switch ($name) {
            case 'date_begin':
            case 'date_end':
                if ($this->name != '') {
                    $dt = new \DateTime($this->$name);
                    return $dt->format(_T('Y-m-d H:i', 'objectslend'));
                }
                return '';
            case 'date_begin_short':
            case 'date_forecast':
                if ($this->$name != '') {
                    $dt = new \DateTime($this->$name);
                    return $dt->format(_T('Y-m-d'));
                }
                return '';
            default:
                return $this->$name;
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
        switch ($name) {
            case 'adherent_id':
                if ((int)$value > 0) {
                    $this->$name = (int)$value;
                } else {
                    $this->$name = null;
                }
                break;
            default:
                $this->$name = $value;
                break;
        }
    }
}
