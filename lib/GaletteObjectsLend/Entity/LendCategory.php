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
use Galette\Core\Plugins;
use Laminas\Db\Sql\Predicate;

/**
 * Categories management
 *
 * @author Mélissa Djebel <melissa.djebel@gmx.net>
 * @author Johan Cwiklinski <johan@x-tnd.be>
 *
 * @property bool $is_active
 * @property string $name
 * @property int $category_id
 * @property CategoryPicture $picture
 */
class LendCategory
{
    public const TABLE = 'category';
    public const PK = 'category_id';

    /** @var array<string> */
    private array $fields = array(
        'category_id' => 'integer',
        'name' => 'varchar(100)',
        'is_active' => 'boolean'
    );
    private int $category_id;
    private ?string $name = null;
    private bool $is_active = true;
    private int $objects_nb = 0;
    private float $objects_price_sum = 0.0;
    // Used to have an url for the image
    private string $categ_image_url = '';
    private CategoryPicture $picture;

    /** @var array<string, bool> */
    private array $deps = [
        'picture'   => true
    ];

    private Db $zdb;
    private Plugins $plugins;

    /**
     * Default constructor
     *
     * @param Db                                      $zdb     Database instance
     * @param Plugins                                 $plugins Plugins instance
     * @param int|ArrayObject<string,int|string>|null $args    Maybe null, an RS object or an id from database
     * @param array<string,bool>                      $deps    Dependencies configuration, see LendCategory::$deps
     */
    public function __construct(Db $zdb, Plugins $plugins, int|ArrayObject $args = null, array $deps = null)
    {
        $this->zdb = $zdb;
        $this->plugins = $plugins;

        if ($deps !== null) {
            $this->deps = array_merge(
                $this->deps,
                $deps
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
     * @param ArrayObject<string, int|string> $r the resultset row
     *
     * @return void
     */
    private function loadFromRS(ArrayObject $r): void
    {
        $this->category_id = (int)$r->category_id;
        $this->name = $r->name;
        $this->is_active = $r->is_active == '1';

        if (property_exists($r, 'objects_count')) {
            $this->objects_nb = (int)$r->objects_count;
        }

        if (property_exists($r, 'objects_price_sum') && $r->objects_price_sum !== null) {
            $this->objects_price_sum = (float)$r->objects_price_sum;
        }

        if ($this->deps['picture'] === true) {
            $this->picture = new CategoryPicture($this->plugins, (int)$this->category_id);
        }
    }

    /**
     * Store category
     *
     * @return bool
     */
    public function store(): bool
    {
        try {
            $values = array();

            foreach ($this->fields as $k => $v) {
                if ($k === 'is_active' && $this->$k === false) {
                    //Handle booleans for postgres ; bugs #18899 and #19354
                    $values[$k] = $this->zdb->isPostgres() ? 'false' : 0;
                } else {
                    $values[$k] = $this->$k ?? null;
                }
            }

            if (!isset($this->category_id) || $this->category_id == '') {
                unset($values['category_id']);
                $insert = $this->zdb->insert(LEND_PREFIX . self::TABLE)
                        ->values($values);
                $result = $this->zdb->execute($insert);
                if ($result->count() > 0) {
                    if ($this->zdb->isPostgres()) {
                        /** @phpstan-ignore-next-line */
                        $this->category_id = (int)$this->zdb->driver->getLastGeneratedValue(
                            PREFIX_DB . 'lend_category_id_seq'
                        );
                    } else {
                        $this->category_id = (int)$this->zdb->driver->getLastGeneratedValue();
                    }
                } else {
                    throw new \RuntimeException('Unable to add category!');
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
     * Drop a category. All objects for removed category will be assigned to none.
     *
     * @return boolean
     */
    public function delete(): bool
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
     * @param bool $count Whether to display count along with name (defaults to true)
     *
     * @return string
     */
    public function getName(bool $count = true): string
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
     * @param string $name name of the property we want to retrieve
     *
     * @return mixed the called property
     */
    public function __get(string $name): mixed
    {
        switch ($name) {
            case 'objects_price_sum':
                return number_format($this->$name, 2, ',', '');
            case 'is_active':
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
        $this->$name = $value;
    }

    /**
     * Get object ID
     *
     * @return ?int
     */
    public function getId(): ?int
    {
        return $this->category_id ?? null;
    }

    /**
     * Is object active
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }

    /**
     * Get picture
     *
     * @return ?CategoryPicture
     */
    public function getPicture(): ?CategoryPicture
    {
        return $this->picture ?? null;
    }

    /**
     * Get sum
     *
     * @return float
     */
    public function getSum(): float
    {
        return $this->objects_price_sum;
    }

    /**
     * Get objects count
     *
     * @return int
     */
    public function getObjectsNb(): int
    {
        return $this->objects_nb;
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
