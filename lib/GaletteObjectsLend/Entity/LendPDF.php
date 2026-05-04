<?php

/**
 * This file is part of Galette Objects Lend plugin (https://galette.eu).
 * SPDX-FileCopyrightText: Copyright © 2013-2026 The Galette Team
 * SPDX-License-Identifier: GPL-3.0-or-later
 */

declare(strict_types=1);

namespace GaletteObjectsLend\Entity;

use Galette\Core\Preferences as CorePreferences;
use Galette\Entity\PdfModel;

/**
 * PDF creation
 *
 * @author Mélissa Djebel <melissa.djebel@gmx.net>
 * @author Johan Cwiklinski <johan@x-tnd.be>
 */
class LendPDF extends \Galette\IO\Pdf
{
    /**
     * Main constructor, set creator and author
     *
     * @param CorePreferences $prefs Preferences
     * @param ?PdfModel       $model Related model
     */
    public function __construct(CorePreferences $prefs, ?PdfModel $model = null)
    {
        parent::__construct($prefs, $model);
        $this->SetDisplayMode('real', 'OneColumn');
    }
}
