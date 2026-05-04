<?php

/**
 * This file is part of Galette Objects Lend plugin (https://galette.eu).
 * SPDX-FileCopyrightText: Copyright © 2013-2026 The Galette Team
 * SPDX-License-Identifier: GPL-3.0-or-later
 */

declare(strict_types=1);

namespace GaletteObjectsLend\Controllers;

use DI\Attribute\Inject;
use Galette\Controllers\AbstractPluginController;
use Galette\Entity\ContributionsTypes;
use GaletteObjectsLend\Entity\Preferences;
use Slim\Psr7\Request;
use Slim\Psr7\Response;

/**
 * Galette objects lend main controller
 *
 * @author Johan Cwiklinski <johan@x-tnd.be>
 */

class MainController extends AbstractPluginController
{
    /**
     * @var array<string,mixed>
     */
    #[Inject("Plugin Galette Objects Lend")]
    protected array $module_info;

    /**
     * Objects lends preferences
     *
     * @param Request  $request  PSR Request
     * @param Response $response PSR Response
     */
    public function preferences(Request $request, Response $response): Response
    {
        if ($this->session->objectslend_preferences !== null) {
            $lendsprefs = $this->session->objectslend_preferences;
            $this->session->objectslend_preferences = null;
        } else {
            $lendsprefs = new Preferences($this->zdb);
        }

        $ctypes = new ContributionsTypes($this->zdb);
        $types_list = $ctypes->getList();
        $ctypes_params = [0 => _T("Choose a contribution type", "objectslend")];
        foreach ($types_list as $id => $type) {
            $ctypes_params[$id] = $type['label'];
        }

        $params = [
            'page_title'    => _T('ObjectsLend preferences', 'objectslend'),
            'type_cotis_options'        => $ctypes->getList(),
            'ctypes'        => $ctypes_params,
            'lendsprefs'    => $lendsprefs->getPreferences()
        ];

        // display page
        $this->view->render(
            $response,
            $this->getTemplate('preferences'),
            $params
        );
        return $response;
    }

    /**
     * Objects lends preferences
     *
     * @param Request  $request  PSR Request
     * @param Response $response PSR Response
     */
    public function storePreferences(Request $request, Response $response): Response
    {
        $post = $request->getParsedBody();
        $lendsprefs = new Preferences($this->zdb);

        $error_detected = [];
        if ($lendsprefs->store($post)) {
            $this->flash->addMessage(
                'success_detected',
                _T("Preferences have been successfully stored!", "objectslend")
            );
        } else {
            $this->session->objectslend_preferences = $lendsprefs;
            foreach ($error_detected as $error) {
                $this->flash->addMessage(
                    'error_detected',
                    $error
                );
            }
        }

        return $response
            ->withStatus(301)
            ->withHeader(
                'Location',
                $this->routeparser->urlFor('objectslend_preferences')
            );
    }
}
