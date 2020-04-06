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

namespace GaletteObjectsLend\Entity;

use Analog\Analog;
use Galette\Core\Db;
use Galette\Core\Plugins;
use \Zend\Db\Sql\Predicate;
use GaletteObjectsLend\Entity\CategoryPicture;

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

    private $deps = [
        'picture'   => true
    ];

    private $zdb;
    private $plugins;

    /**
     * Default constructor
     *
     * @param Db         $zdb     Database instance
     * @param Plugins    $plugins Pluginsugins instance
     * @param int|object $args    Maybe null, an RS object or an id from database
     * @param array      $deps    Dependencies configuration, see LendCategory::$deps
     */
    public function __construct(Db $zdb, Plugins $plugins, $args = null, $deps = null)
    {
        $this->zdb = $zdb;
        $this->plugins = $plugins;

        if ($deps !== null && is_array($deps)) {
            $this->deps = array_merge(
                $this->deps,
                $deps
            );
        } elseif ($deps !== null) {
            Analog::log(
                '$deps should be an array, ' . gettype($deps) . ' given!',
                Analog::WARNING
            );
        }

        if ($this->deps['picture'] === true) {
            $this->picture = new CategoryPicture($this->plugins);
        }

        if (is_int($args)) {
            try {
                $select = $this->zdb->select(LEND_PREFIX . self::TABLE)
                        ->where(array(self::PK => $args));
                $results = $this->zdb->execute($select);
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
        $this->category_id = $r->category_id;
        $this->name = $r->name;
        $this->is_active = $r->is_active == '1' ? true : false;

        if (property_exists($r, 'objects_count')) {
            $this->objects_nb = $r->objects_count;
        }

        if (property_exists($r, 'objects_price_sum')) {
            $this->objects_price_sum = $r->objects_price_sum;
        }


        if ($this->deps['picture'] === true) {
            $this->picture = new CategoryPicture($this->plugins, (int)$this->category_id);
        }
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
                if ($k === 'is_active' && $this->$k === false) {
                    //Handle booleans for postgres ; bugs #18899 and #19354
                    $values[$k] = $this->zdb->isPostgres() ? 'false' : 0;
                } else {
                    $values[$k] = $this->$k;
                }
            }

            if (!isset($this->category_id) || $this->category_id == '') {
                unset($values['category_id']);
                $insert = $this->zdb->insert(LEND_PREFIX . self::TABLE)
                        ->values($values);
                $result = $this->zdb->execute($insert);
                if ($result->count() > 0) {
                    if ($this->zdb->isPostgres()) {
                        $this->category_id = $this->zdb->driver->getLastGeneratedValue(
                            PREFIX_DB . 'lend_category_id_seq'
                        );
                    } else {
                        $this->category_id = $this->zdb->driver->getLastGeneratedValue();
                    }
                } else {
                    throw new \RuntimeException('Unable to add catagory!');
                }
            } else {
                $update = $this->zdb->update(LEND_PREFIX . self::TABLE)
                        ->set($values)
                        ->where(array(self::PK => $this->category_id));
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
     * Drop a category. All objects for removed catagory will be assigned to none.
     *
     * @return boolean
     */
    public function delete()
    {
        try {
            $this->zdb->connection->beginTransaction();
            $select = $this->zdb->select(LEND_PREFIX . LendObject::TABLE)
                    ->where(array('category_id' => $this->category_id));
            $results = $this->zdb->execute($select);
            if ($results->count() > 0) {
                $values = ['category_id' => new Predicate\Expression('NULL')];
                $update = $this->zdb->update(LEND_PREFIX . LendObject::TABLE)
                        ->set($values)
                        ->where(array('category_id' => $this->category_id));
                $this->zdb->execute($update);
            }

            $delete = $this->zdb->delete(LEND_PREFIX . self::TABLE)
                    ->where(array(self::PK => $this->category_id));
            $this->zdb->execute($delete);
            $this->zdb->connection->commit();
            return true;
        } catch (\Exception $e) {
            $this->zdb->connection->rollBack();
            Analog::log(
                'Something went wrong :\'( | ' . $e->getMessage() . "\n" .
                $e->getTraceAsString(),
                Analog::ERROR
            );
            return false;
        }
    }

    /**
     * Get category name
     *
     * @param boolea $count Whether to display count along with name (defaults to true)
     *
     * @return string
     */
    public function getName($count = true)
    {
        $name = $this->name !== null ? $this->name : _T("No category", "objectslend");

        if ($count === true) {
            $name .= " ({$this->objects_nb})";
        }

        return $name;
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
