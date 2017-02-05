<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Public Class LendCategory
 * Store informations about a lend category
 *
 * PHP version 5
 *
 * Copyright © 2013-2016 Mélissa Djebel
 * Copyright © 2017 The Galette Team
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
 * Copyright © 2017 The Galette Team
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL License 3.0 or (at your option) any later version
 * @version   0.7
 * @link      http://galette.tuxfamily.org
 * @since     Available since 0.7
 */

namespace GaletteObjectsLend;

use Analog\Analog;
use \Zend\Db\Sql\Predicate;

class LendCategory
{
    const TABLE = 'category';
    const PK = 'category_id';

    private $fields = array(
        'category_id' => 'integer',
        'name' => 'varchar(100)',
        'is_active' => 'boolean'
    );
    private $category_id;
    private $name = '';
    private $is_active = true;
    private $objects_nb = 0;
    private $objects_price_sum = 0;
    // Used to have an url for the image
    private $categ_image_url = '';
    private $picture;

    /**
     * Construit un nouveau statut d'emprunt à partir de la BDD (à partir de son ID) ou vierge
     *
     * @param int|object $args Peut être null, un ID ou une ligne de la BDD
     */
    public function __construct($args = null)
    {
        global $zdb, $plugins;

        $this->picture = new CategoryPicture($plugins);

        if (is_int($args)) {
            try {
                $select = $zdb->select(LEND_PREFIX . self::TABLE)
                        ->where(array(self::PK => $args));
                $results = $zdb->execute($select);
                if ($results->count() == 1) {
                    $this->loadFromRS($results->current());
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
        global $plugins;

        $this->category_id = $r->category_id;
        $this->name = $r->name;
        $this->is_active = $r->is_active == '1' ? true : false;

        $this->picture = new CategoryPicture($plugins, (int)$this->category_id);
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
            $values = array();

            foreach ($this->fields as $k => $v) {
                $values[$k] = $this->$k;
            }

            if (!isset($this->category_id) || $this->category_id == '') {
                $insert = $zdb->insert(LEND_PREFIX . self::TABLE)
                        ->values($values);
                $add = $zdb->execute($insert);
                if ($add > 0) {
                    $this->category_id = $zdb->driver->getLastGeneratedValue();
                } else {
                    throw new \RuntimeException('Unable to add catagory!');
                }
            } else {
                $update = $zdb->update(LEND_PREFIX . self::TABLE)
                        ->set($values)
                        ->where(array(self::PK => $this->category_id));
                $zdb->execute($update);
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
     * Renvoi toutes les categories triées par le tri indiqué
     *
     * @param string $tri       Colonne de tri
     * @param string $direction asc ou desc
     *
     * @return LendCategory[] La liste des statuts triés par le tri donné
     */
    public static function getAllCategories($tri, $direction)
    {
        global $zdb;

        try {
            $select = $zdb->select(LEND_PREFIX . self::TABLE)
                    ->order($tri . ' ' . $direction);

            $categs = array();
            $result = $zdb->execute($select);
            foreach ($result as $r) {
                $categs[] = new LendCategory($r);
            }
            return $categs;
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
     * Get all active categories sort by name with number of objects associated
     *
     * @param boolean $noobjects Retrieve categories with no objects associated, defaults to true
     *
     * @return LendCategory[]
     */
    public static function getActiveCategories($noobjects = true)
    {
        global $zdb;

        try {
            $select_count = $zdb->select(LEND_PREFIX . LendObject::TABLE)
                ->columns(array(new Predicate\Expression('count(*)')))
                ->where(
                    array(
                        'is_active' => 1,
                        new Predicate\Expression(
                            PREFIX_DB . LEND_PREFIX . LendObject::TABLE . '.category_id = ' .
                            PREFIX_DB . LEND_PREFIX . self::TABLE . '.' . self::PK
                        )
                    )
                );

            $select_sum = $zdb->select(LEND_PREFIX . LendObject::TABLE)
                ->columns(array(new Predicate\Expression('sum(price)')))
                ->where(
                    array(
                        'is_active' => 1,
                        new Predicate\Expression(
                            PREFIX_DB . LEND_PREFIX . LendObject::TABLE . '.category_id = ' .
                            PREFIX_DB . LEND_PREFIX . self::TABLE . '.' . self::PK
                        )
                    )
                );

            $where = ['is_active' => 1];
            $having = [];
            if ($noobjects === false) {
                $having[] = new Predicate\Operator(
                    'nb',
                    '>',
                    '0'
                );
            }
            $select = $zdb->select(LEND_PREFIX . self::TABLE)
                ->columns(
                    array(
                        '*',
                        'nb' => new Predicate\Expression(
                            '(' . $zdb->sql->getSqlStringForSqlObject($select_count) . ')'
                        ),
                        'sum' => new Predicate\Expression(
                            '(' . $zdb->sql->getSqlStringForSqlObject($select_sum) . ')'
                        ),
                    )
                )
                ->where($where)
                ->having($having)
                ->order('name');

            $categs = array();
            $result = $zdb->execute($select);
            foreach ($result as $r) {
                $cat = new LendCategory($r);
                $cat->objects_nb = $r->nb;
                if (is_numeric($r->sum)) {
                    $cat->objects_price_sum = $r->sum;
                }
                $categs[] = $cat;
            }
            return $categs;
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
     * Renvoi toutes les categories actives triés par nom avec le nombre
     * d'objet qui correspond à la chaine recherchée
     *
     * @param string $search Chaine de recherche
     * @return LendCategory[] La liste des categories actives triées
     */
    public static function getActiveCategoriesWithSearchCriteria($search)
    {
        if (strlen($search) < 1) {
            return self::getActiveCategories();
        }

        global $zdb;

        try {
            $select_count = $zdb->select(LEND_PREFIX . LendObject::TABLE)
                ->columns(array(new Predicate\Expression('count(*)')))
                ->where(
                    array(
                        'is_active' => 1,
                        LendObject::writeWhereQuery($search),
                        new Predicate\Expression(
                            PREFIX_DB . LEND_PREFIX . LendObject::TABLE . '.category_id = ' .
                            PREFIX_DB . LEND_PREFIX . self::TABLE . '.' . self::PK
                        )
                    )
                );

            $select = $zdb->select(LEND_PREFIX . self::TABLE)
                ->columns(
                    array(
                        '*',
                        'nb' => new Predicate\Expression(
                            '(' . $zdb->sql->getSqlStringForSqlObject($select_count) . ')'
                        )
                    )
                )
                ->where(array('is_active' => 1))
                ->order('name');

            $categs = array();
            $result = $zdb->execute($select);
            foreach ($result as $r) {
                $cat = new LendCategory($r);
                $cat->objects_nb = $r->nb;
                $categs[] = $cat;
            }
            return $categs;
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
     * Supprime une catégorie et assigne les objets de cette catégorie à "aucune catégorie"
     *
     * @param int $id Id de la catégorie à supprimer
     *
     * @return boolean True en cas de réussite, false sinon
     */
    public static function deleteCategory($id)
    {
        global $zdb;

        try {
            $select = $zdb->select(LEND_PREFIX . LendObject::TABLE)
                    ->where(array('category_id' => $id));
            $results = $zdb->execute($select);
            if ($results->count() > 0) {
                $values = ['category_id' => new Predicate\Expression('NULL')];
                $update = $zdb->update(LEND_PREFIX . LendObject::TABLE)
                        ->set($values)
                        ->where(array('category_id' => $id));
                $zdb->execute($update);
            }

            $delete = $zdb->delete(PREFIX_DB . LEND_PREFIX . self::TABLE)
                    ->where(array(self::PK => $id));
            $zdb->execute($delete);
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
            case 'objects_price_sum':
                return number_format($this->$name, 2, ',', '');
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
        $this->$name = $value;
    }
}
