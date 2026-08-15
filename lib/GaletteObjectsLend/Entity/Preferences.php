<?php

/**
 * This file is part of Galette Objects Lend plugin (https://galette.eu).
 * SPDX-FileCopyrightText: Copyright © 2013-2026 The Galette Team
 * SPDX-License-Identifier: GPL-3.0-or-later
 */

declare(strict_types=1);

namespace GaletteObjectsLend\Entity;

use Analog\Analog;
use Galette\Core\Db;

/**
 * Plugin preferences
 *
 * @author Mélissa Djebel <melissa.djebel@gmx.net>
 * @author Johan Cwiklinski <johan@x-tnd.be>
 *
 * @property bool   $ENABLE_MEMBER_RENT_OBJECT
 * @property bool   $AUTO_GENERATE_CONTRIBUTION
 * @property int    $GENERATED_CONTRIBUTION_TYPE_ID
 * @property string $GENERATED_CONTRIB_INFO_TEXT
 * @property bool   $VIEW_CATEGORY
 * @property bool   $VIEW_SERIAL
 * @property bool   $VIEW_THUMBNAIL
 * @property bool   $VIEW_DESCRIPTION
 * @property bool   $VIEW_PRICE
 * @property bool   $VIEW_DIMENSION
 * @property bool   $VIEW_WEIGHT
 * @property bool   $VIEW_LEND_PRICE
 * @property bool   $VIEW_DATE_FORECAST
 * @property bool   $VIEW_LIST_PRICE_SUM
 * @property int    $THUMB_MAX_WIDTH
 * @property int    $THUMB_MAX_HEIGHT
 */
class Preferences
{
    public const string TABLE = 'parameters';
    public const string PK = 'code';

    private Db $zdb;
    /** @var array<string,mixed> */
    private array $prefs = [];

    /**
     * Show categories at the top of the objects list
     * Valeur : 0 = false / 1 = true
     */
    public const string PARAM_VIEW_CATEGORY = 'VIEW_CATEGORY';

    /**
     * Show serial number column
     * Valeur : 0 = false / 1 = true
     */
    public const string PARAM_VIEW_SERIAL = 'VIEW_SERIAL';

    /**
     * Show thumbnail column
     * Valeur : 0 = false / 1 = true
     */
    public const string PARAM_VIEW_THUMBNAIL = 'VIEW_THUMBNAIL';

    /**
     * Show description column
     * Valeur : 0 = false / 1 = true
     */
    public const string PARAM_VIEW_DESCRIPTION = 'VIEW_DESCRIPTION';

    /**
     * Show price column
     * Valeur : 0 = false / 1 = true
     */
    public const string PARAM_VIEW_PRICE = 'VIEW_PRICE';

    /**
     * Show dimensions column
     * Valeur : 0 = false / 1 = true
     */
    public const string PARAM_VIEW_DIMENSION = 'VIEW_DIMENSION';

    /**
     * Show weight column
     * Valeur : 0 = false / 1 = true
     */
    public const string PARAM_VIEW_WEIGHT = 'VIEW_WEIGHT';

    /**
     * Show rent price column
     * Valeur : 0 = false / 1 = true
     */
    public const string PARAM_VIEW_LEND_PRICE = 'VIEW_LEND_PRICE';

    /**
     * Show previsional return date column
     * Valeur : 0 = false / 1 = true
     */
    public const string PARAM_VIEW_DATE_FORECAST = 'VIEW_DATE_FORECAST';

    /**
     * Show the sum of prices on the list of objects
     * Valeur : 0 = false / 1 = true
     */
    public const string PARAM_VIEW_LIST_PRICE_SUM = 'VIEW_LIST_PRICE_SUM';

    /**
     * Maximum width of a thumbnail (applied to objects/categories)
     * Valeur : largeur en pixels
     */
    public const string PARAM_THUMB_MAX_WIDTH = 'THUMB_MAX_WIDTH';

    /**
     * Maximum height of a thumbnail (applied to objects/categories)
     * Valeur : largeur en pixels
     */
    public const string PARAM_THUMB_MAX_HEIGHT = 'THUMB_MAX_HEIGHT';

    /**
     * Generate automatically a contribution when an object is rented
     * Valeur : 0 = false / 1 = true
     */
    public const string PARAM_AUTO_GENERATE_CONTRIBUTION = 'AUTO_GENERATE_CONTRIBUTION';

    /**
     * Contribution ID to generate when an object is rented
     * Valeur : ID du type de contribution
     */
    public const string PARAM_GENERATED_CONTRIBUTION_TYPE_ID = 'GENERATED_CONTRIBUTION_TYPE_ID';

    /**
     * Text for the contribution
     * Valeur : texte d'info à mettre avec des placeholders à remplacer
     */
    public const string PARAM_GENERATED_CONTRIB_INFO_TEXT = 'GENERATED_CONTRIB_INFO_TEXT';

    /**
     * Allow non staff members to rent objects
     * Valeur : 0 = false / 1 = true
     */
    public const string PARAM_ENABLE_MEMBER_RENT_OBJECT = 'ENABLE_MEMBER_RENT_OBJECT';

    /**
     * Default constructor
     *
     * @param Db   $zdb  Db instance
     * @param bool $load Automatically load preferences on load
     *
     * @return void
     */
    public function __construct(Db $zdb, bool $load = true)
    {
        $this->zdb = $zdb;
        if ($load) {
            $this->load();
        }
    }

    /**
     * Get preferences
     *
     * @return array<string, mixed>
     */
    public function getPreferences(): array
    {
        if (count($this->prefs) == 0) {
            $this->load();
        }
        return $this->prefs;
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
        if (isset($this->prefs[$name])) {
            return $this->prefs[$name];
        }

        $msg = __CLASS__ . '::' . $name . ' is not set';
        Analog::log(
            $msg,
            Analog::INFO
        );
        throw new \RuntimeException($msg);
    }

    /**
     * Store preferences
     *
     * @param array<string,mixed> $data Posted data
     */
    public function store(array $data): bool
    {
        foreach ($data as $key => $value) {
            $this->prefs[$key] = $value;
        }

        $need_transaction = !$this->zdb->inTransaction();
        try {
            if ($need_transaction) {
                $this->zdb->beginTransaction();
            }
            $update = $this->zdb->update(LEND_PREFIX . self::TABLE);
            $update->set(
                [
                    'value_text'        => $data['GENERATED_CONTRIB_INFO_TEXT'],
                    'date_modification' => date('Y-m-d H:i:s')
                ]
            )->where->equalTo(self::PK, 'GENERATED_CONTRIB_INFO_TEXT');
            $this->zdb->execute($update);

            $update = $this->zdb->update(LEND_PREFIX . self::TABLE);
            $update->set(
                [
                    'value_numeric'     => ':value_numeric',
                    'date_modification' => ':date_modification'
                ]
            )->where->equalTo(self::PK, ':' . self::PK);
            $stmt = $this->zdb->sql->prepareStatementForSqlObject($update);

            unset($data['GENERATED_CONTRIB_INFO_TEXT']);
            foreach ($data as $key => $value) {
                $stmt->execute(
                    [
                        'value_numeric'     => $value,
                        'date_modification' => date('Y-m-d H:i:s'),
                        self::PK            => $key
                    ]
                );
            }

            if ($need_transaction) {
                $this->zdb->commit();
            }
            return true;
        } catch (\Exception $e) {
            if ($need_transaction) {
                $this->zdb->rollback();
            }
            throw $e;
        }
    }

    /**
     * Load current preferences from database.
     */
    public function load(): bool
    {
        $this->prefs = [];

        try {
            $result = $this->zdb->selectAll(LEND_PREFIX . self::TABLE);
            foreach ($result as $pref) {
                $pk_field = self::PK;
                $value_field = 'value_numeric';
                if ($pref->is_text == '1') {
                    $value_field = 'value_text';
                }
                $this->prefs[$pref->$pk_field] = $pref->$value_field;
            }
            return true;
        } catch (\Exception $e) {
            Analog::log(
                'ObjectsLend Preferences cannot be loaded. Plugin should not work without '
                . 'it. Exiting. ' . $e->getMessage(),
                Analog::URGENT
            );
            return false;
        }
    }

    /**
     * Get thumbnail maximum with
     */
    public function getThumbWidth(): int
    {
        return (int)$this->prefs['THUMB_MAX_WIDTH'];
    }

    /**
     * Get thumbnail maximum height
     */
    public function getThumbHeight(): int
    {
        return (int)$this->prefs['THUMB_MAX_HEIGHT'];
    }

    /**
     * Whether to display images (as thumbnails) in lists
     */
    public function imagesInLists(): bool
    {
        return (bool)$this->prefs['VIEW_THUMBNAIL'];
    }

    /**
     * Shall we show fullsize images?
     *
     * Would appear editing object, and on thumbnails click
     */
    public function showFullsize(): bool
    {
        return (bool)$this->prefs['VIEW_FULLSIZE'];
    }
}
