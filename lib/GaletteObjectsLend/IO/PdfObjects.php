<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Objects PDF list
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
 * @category  IO
 * @package   GaletteObjectsLend
 *
 * @author    Johan Cwiklinski <johan@x-tnd.be>
 * @copyright 2018 The Galette Team
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL License 3.0 or (at your option) any later version
 * @link      http://galette.tuxfamily.org
 */

namespace GaletteObjectsLend\IO;

use Galette\Core\Db;
use Galette\IO\Pdf;
use Galette\Core\Preferences;
use Galette\Core\Login;
use Analog\Analog;
use GaletteObjectsLend\Filters\ObjectsList;
use GaletteObjectsLend\Entity\LendCategory;
use GaletteObjectsLend\Entity\Preferences as LendPreferences;

/**
 * Object labels PDF
 *
 * @category  IO
 * @name      PDFObjects
 * @package   GaletteObjectsLend
 * @author    Johan Cwiklinski <johan@x-tnd.be>
 * @copyright 2018 The Galette Team
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL License 3.0 or (at your option) any later version
 * @link      http://galette.tuxfamily.org
 */
class PdfObjects extends Pdf
{
    const LIST_FONT = self::FONT_SIZE-2;

    private $zdb;
    private $lendsprefs;
    private $filters;
    private $login;

    /**
     * Main constructor, set creator and author
     *
     * @param Db              $zdb        Database instance
     * @param Preferences     $prefs      Preferences
     * @param LendPreferences $lendsprefs Plugin preferences
     * @param ObjectsList     $filters    Current filters
     * @param Login           $login      Login instance
     */
    public function __construct(
        Db $zdb,
        Preferences $prefs,
        LendPreferences $lendsprefs,
        ObjectsList $filters,
        Login $login
    ) {
        parent::__construct($prefs);
        $this->zdb = $zdb;
        $this->lendsprefs = $lendsprefs;
        $this->filters = $filters;
        $this->login = $login;
        $this->init();
    }

    /**
     * Initialize PDF
     *
     * @return void
     */
    private function init()
    {
        // Set document information
        $this->SetTitle(_T("Objects list", "objectslend"));
        $this->SetSubject(_T("Generated by Galette"));
        $this->SetKeywords(_T("Objects list", "objectslend"));

        $this->setPageOrientation('L');
        $this->setHeaderMargin(10);
    }

    /**
     * Page header
     *
     * @return void
     *
     * @phpcs:disable
     */
    public function Header()
    {
        // @phpcs:enable
        $this->SetFont(Pdf::FONT, 'B');
        $x = $this->getX();
        $this->Cell(0, 10, _T("Objects list", "objectslend"), 0, false, 'C', false, '', 0, false, 'M', 'M');
        $this->SetFont(Pdf::FONT, '', self::LIST_FONT);
        $this->setX($x);
        $this->Cell(
            0,
            10,
            str_replace(
                '%date',
                date(_T("Y-m-d")),
                _T("Printed on %date", "objectslend")
            ),
            0,
            false,
            'R',
            false,
            '',
            0,
            false,
            'M',
            'M'
        );
    }

    /**
     * Draw objects list
     *
     * @param LendObject[] $objects List of objects
     *
     * @return void
     */
    public function drawList($objects)
    {
        $this->Open();
        $this->AddPage();

        $this->Ln(10); //for Header

        if ($this->filters->category_filter > 0) {
            $category = new LendCategory((int)$filters->category_filter);
        }

        // Header
        $this->SetFillColor(255, 255, 255);

        $w_checkbox = 5;
        $w_name = 33;
        $w_description = 45;
        $w_serial = 21;
        $w_price = 17;
        $w_dimension = 28;
        $w_weight = 16;
        $w_status = 26;
        $w_date = 22;
        $w_adherent = 26;

        $this->Cell($w_checkbox, 0, $this->stretchHead('', $w_checkbox), 1, 0, 'C', 1);
        $this->Cell($w_name, 0, $this->stretchHead(_T("Name", "objectslend"), $w_name), 1, 0, 'C', 1);
        $this->Cell($w_description, 0, $this->stretchHead(_T("Description", "objectslend"), $w_description), 1, 0, 'C', 1);
        $this->Cell($w_serial, 0, $this->stretchHead(_T("Serial", "objectslend"), $w_serial), 1, 0, 'C', 1);
        $this->Cell($w_price, 0, $this->stretchHead(_T("Price", "objectslend"), $w_price), 1, 0, 'C', 1);
        $this->Cell($w_price, 0, $this->stretchHead(_T("Borrow price", "objectslend"), $w_price), 1, 0, 'C', 1);
        $this->Cell($w_dimension, 0, $this->stretchHead(_T("Dimensions", "objectslend"), $w_dimension), 1, 0, 'C', 1);
        $this->Cell($w_weight, 0, $this->stretchHead(_T("Weight", "objectslend"), $w_weight), 1, 0, 'C', 1);
        $this->Cell($w_status, 0, $this->stretchHead(_T("Status", "objectslend"), $w_status), 1, 0, 'C', 1);
        $this->Cell($w_date, 0, $this->stretchHead(_T("Since", "objectslend"), $w_date), 1, 0, 'C', 1);
        $this->Cell($w_adherent, 0, $this->stretchHead(_T("Member", "objectslend"), $w_adherent), 1, 0, 'C', 1);
        $this->Cell($w_date, 0, $this->stretchHead(_T("Return", "objectslend"), $w_date), 1, 1, 'C', 1);

        $this->SetFont('');

        $current_category = -1;
        $sum_price = 0;
        $grant_total = 0;
        $row = 0;

        foreach ($objects as $object) {
            if ($this->lendsprefs->{LendPreferences::PARAM_VIEW_CATEGORY}
                && $current_category !== $object->category_id
            ) {
                $this->SetFont('', 'B');

                if (($this->login->isAdmin() || $this->login->isStaff()) && $sum_price > 0) {
                    $width = $w_checkbox + $w_name + $w_description + $w_serial + $w_price;
                    $this->Cell($width, 0, number_format($sum_price, 2, ',', ''), '', 0, 'R');
                    $sum_price = 0;
                    $this->Ln();
                }

                if (!empty($object->category_id)) {
                    $category = new LendCategory($this->zdb, $this->plugins, (int)$object->category_id);
                    $text = str_replace(
                        '%category',
                        $category->name,
                        _T("Category: %category", "objectslend")
                    );
                } else {
                    $text = _T("No category");
                }

                $this->Cell(0, 0, $text, 0, 1, 'C');
                $this->SetFont('');
            }

            if ($row++ % 2 == 0) {
                $this->SetFillColor(255, 189, 64);
            } else {
                $this->SetFillColor(255, 214, 135);
            }

            $fill = !$object->is_home_location;
            $this->Cell($w_checkbox, 0, '□', 'B', 0, 'L', $fill);
            $this->Cell($w_name, 0, $this->cut($object->name, $w_name), 'B', 0, 'L', $fill);
            $this->Cell($w_description, 0, $this->cut($object->description, $w_description), 'B', 0, 'L', $fill);
            $this->Cell($w_serial, 0, $this->cut($object->serial_number, $w_serial), 'B', 0, 'L', $fill);
            $this->Cell($w_price, 0, $this->cut($object->price, $w_price), 'B', 0, 'R', $fill);
            $this->Cell($w_price, 0, $this->cut($object->rent_price, $w_price).$object->currency, 'B', 0, 'R', $fill);
            $this->Cell($w_dimension, 0, $this->cut($object->dimension, $w_dimension), 'B', 0, 'L', $fill);
            $this->Cell($w_weight, 0, $this->cut($object->weight, $w_weight), 'B', 0, 'R', $fill);
            $this->Cell($w_status, 0, $this->cut($object->status_text, $w_status), 'B', 0, 'L', $fill);
            $this->Cell($w_date, 0, $this->cut($object->date_begin_short, $w_date), 'B', 0, 'L', $fill);
            $this->Cell($w_adherent, 0, $this->cut($object->nom_adh . ' ' . $object->prenom_adh, $w_adherent), 'B', 0, 'L', $fill);
            $this->Cell($w_date, 0, $this->cut($object->date_forecast_short, $w_date), 'B', 1, 'L', $fill);

            if ($this->login->isAdmin() || $this->login->isStaff()) {
                $sum_price += (float)str_replace(array(',', ' '), array('.', ''), $object->price);
                $grant_total += (float)str_replace(array(',', ' '), array('.', ''), $object->price);
            }
        }

        if (($this->login->isAdmin() || $this->login->isStaff())) {
            $width = $w_checkbox + $w_name + $w_description + $w_serial + $w_price;
            $this->Cell($width, 0, number_format($sum_price, 2, ',', ''), '', 0, 'R');
            $this->Ln();
            $this->Ln();

            $this->Cell($width, 0, _T("Total:", "objectslend") . ' ' . number_format($grant_total, 2, ',', ''), '', 0, 'R');
            $this->Ln();
        }

        $this->Ln();

        $this->Cell($w_price, 0, '', true, 0, '', true);
        $this->Cell(0, 0, _T("Borrowed", "objectslend"), 0, 1);
        $this->Cell($w_price, 0, '', true);
        $this->Cell(0, 0, _T("Available", "objectslend"), 0, 1);

        $current_category = $object->category_id;
    }

    /**
     * Stretch a header string
     *
     * @param string  $str    Original string
     * @param integer $length Max length
     *
     * @return string
     */
    protected function stretchHead($str, $length)
    {
        $this->SetFont(self::FONT, 'B', self::LIST_FONT);
        $stretch = 100;
        if ($this->GetStringWidth($str) > $length) {
            while ($this->GetStringWidth($str) > $length) {
                $this->setFontStretching(--$stretch);
            }
        }
        return $str;
    }


    /**
     * Cut a string
     *
     * @param string  $str    Original string
     * @param integer $length Max length
     *
     * @return string
     */
    protected function cut($str, $length)
    {
        $length = $length -2; //keep a margin
        if ($this->GetStringWidth($str) > $length) {
            while ($this->GetStringWidth($str . '...') > $length) {
                $str = mb_substr($str, 0, -1, 'UTF-8');
            }
            $str .= '...';
        }
        return $str;
    }
}
