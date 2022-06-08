<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Status controller
 *
 * PHP version 5
 *
 * Copyright © 2021 The Galette Team
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
 * @category  Controllers
 * @package   GaletteObjectsLend
 *
 * @author    Johan Cwiklinski <johan@x-tnd.be>
 * @copyright 2021 The Galette Team
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL License 3.0 or (at your option) any later version
 * @link      http://galette.tuxfamily.org
 * @since     2021-05-12
 */

namespace GaletteObjectsLend\Controllers\Crud;

use Analog\Analog;
use GaletteObjectsLend\Filters\StatusList;
use GaletteObjectsLend\Repository\Status;
use GaletteObjectsLend\Entity\LendStatus;
use GaletteObjectsLend\Entity\Preferences;
use Galette\Controllers\Crud\AbstractPluginController;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Status controller
 *
 * @category  Controllers
 * @name      CategoriesController
 * @package   GaletteObjectsLend
 * @author    Johan Cwiklinski <johan@x-tnd.be>
 * @copyright 2021 The Galette Team
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL License 3.0 or (at your option) any later version
 * @link      http://galette.tuxfamily.org
 * @since     2021-05-09
 */

class StatusController extends AbstractPluginController
{
    /**
     * @Inject("Plugin Galette Objects Lend")
     * @var integer
     */
    protected $module_info;

    // CRUD - Create

    /**
     * Add page
     *
     * @param Request  $request  PSR Request
     * @param Response $response PSR Response
     *
     * @return Response
     */
    public function add(Request $request, Response $response): Response
    {
        return $this->edit($request, $response, null, 'add');
    }

    /**
     * Add action
     *
     * @param Request  $request  PSR Request
     * @param Response $response PSR Response
     *
     * @return Response
     */
    public function doAdd(Request $request, Response $response): Response
    {
        return $this->doEdit($request, $response, null, 'add');
    }

    // /CRUD - Create
    // CRUD - Read

    /**
     * List page
     *
     * @param Request        $request  PSR Request
     * @param Response       $response PSR Response
     * @param string         $option   One of 'page' or 'order'
     * @param string|integer $value    Value of the option
     *
     * @return Response
     */
    public function list(Request $request, Response $response, $option = null, $value = null): Response
    {
        if (isset($this->session->objectslend_filter_statuses)) {
            $filters = $this->session->objectslend_filter_statuses;
        } else {
            $filters = new StatusList();
        }

        if ($option !== null) {
            switch ($option) {
                case 'page':
                    $filters->current_page = (int)$value;
                    break;
                case 'order':
                    $filters->orderby = $value;
                    break;
            }
        }

        $statuses = new Status($this->zdb, $this->login, $filters);
        $list = $statuses->getStatusList(true);

        if (count(LendStatus::getActiveStockStatuses($this->zdb)) == 0) {
            $this->flash->addMessage(
                'error_detected',
                _T("Please add add at last one status \"in stock\"!", "objectslend")
            );
        }
        if (count(LendStatus::getActiveTakeAwayStatuses($this->zdb)) == 0) {
            $this->flash->addMessage(
                'error_detected',
                _T("Please add at least one status \"object borrowed\"!", "objectslend")
            );
        }

        $this->session->objectslend_filter_statuses = $filters;

        //assign pagination variables to the template and add pagination links
        $filters->setViewPagination($this->router, $this->view->getSmarty(), false);

        $lendsprefs = new Preferences($this->zdb);
        // display page
        $this->view->render(
            $response,
            'file:[' . $this->getModuleRoute() . ']status_list.tpl',
            array(
                'page_title'            => _T("Status list", "objectslend"),
                'require_dialog'        => true,
                'statuses'              => $list,
                'nb_status'             => count($list),
                'olendsprefs'           => $lendsprefs,
                'filters'               => $filters,
                'time'                  => time()
            )
        );
        return $response;
    }

    /**
     * Filtering
     *
     * @param Request  $request  PSR Request
     * @param Response $response PSR Response
     *
     * @return Response
     */
    public function filter(Request $request, Response $response): Response
    {
        $post = $request->getParsedBody();
        if (isset($this->session->objectslend_filter_statuses)) {
            $filters = $this->session->objectslend_filter_statuses;
        } else {
            $filters = new StatusList();
        }

        //reintialize filters
        if (isset($post['clear_filter'])) {
            $filters->reinit();
        } else {
            //string to filter
            if (isset($post['filter_str'])) { //filter search string
                $filters->filter_str = stripslashes(
                    htmlspecialchars($post['filter_str'], ENT_QUOTES)
                );
            }
            //activity to filter
            if (isset($post['active_filter'])) {
                if (is_numeric($post['active_filter'])) {
                    $filters->active_filter = $post['active_filter'];
                }
            }
            //stock to filter
            if (isset($post['stock_filter'])) {
                if (is_numeric($post['stock_filter'])) {
                    $filters->stock_filter = $post['stock_filter'];
                }
            }

            //number of rows to show
            if (isset($post['nbshow'])) {
                $filters->show = $post['nbshow'];
            }
        }

        $this->session->objectslend_filter_statuses = $filters;

        return $response
            ->withStatus(301)
            ->withHeader('Location', $this->router->pathFor('objectslend_statuses'));
    }

    // /CRUD - Read
    // CRUD - Update

    /**
     * Edit page
     *
     * @param Request  $request  PSR Request
     * @param Response $response PSR Response
     * @param int|null $id       Model id
     * @param string   $action   Action
     *
     * @return Response
     */
    public function edit(Request $request, Response $response, int $id = null, $action = 'edit'): Response
    {
        if ($this->session->objectslend_status !== null) {
            $status = $this->session->objectslend_status;
            $this->session->objectslend_status = null;
        } else {
            $status = new LendStatus($this->zdb, $id);
        }

        if ($status->status_id !== null) {
            $title = str_replace(
                '%status',
                $status->status_text,
                _T("Edit status %status", "objectslend")
            );
        } else {
            $title = _T("New status", "objectslend");
        }

        $params = [
            'page_title'    => $title,
            'status'        => $status,
            'action'        => $action
        ];

        // display page
        $this->view->render(
            $response,
            'file:[' . $this->getModuleRoute() . ']status_edit.tpl',
            $params
        );
        return $response;
    }

    /**
     * Edit action
     *
     * @param Request  $request  PSR Request
     * @param Response $response PSR Response
     * @param null|int $id       Model id for edit
     * @param string   $action   Either add or edit
     *
     * @return Response
     */
    public function doEdit(Request $request, Response $response, int $id = null, $action = 'edit'): Response
    {
        $post = $request->getParsedBody();
        $status = new LendStatus($this->zdb, $id);
        $error_detected = [];

        $status->status_text = $post['text'];
        $status->in_stock = isset($post['in_stock']);
        $status->is_active = isset($post['is_active']);
        $days = trim($post['rent_day_number']);
        $status->rent_day_number = strlen($days) > 0 ? intval($days) : null;
        if (!$status->store()) {
            $error_detected[] = _T("An error occured while storing the status.", "objectslend");
        }

        if (count($error_detected)) {
            $this->session->objectslend_status = $status;
            foreach ($error_detected as $error) {
                $this->flash->addMessage(
                    'error_detected',
                    $error
                );
            }

            $args = ($action == 'edit' ? ['id' => $status->status_id] : []);
            return $response
                ->withStatus(301)
                ->withHeader(
                    'Location',
                    $this->router->pathFor(
                        'objectslend_status_' . $action,
                        $args
                    )
                );
        } else {
            //redirect to status list
            $this->flash->addMessage(
                'success_detected',
                _T("Status has been saved", "objectslend")
            );

            return $response
                ->withStatus(301)
                ->withHeader(
                    'Location',
                    $this->router->pathFor('objectslend_statuses')
                );
        }
    }

    // /CRUD - Update
    // CRUD - Delete

    /**
     * Get redirection URI
     *
     * @param array $args Route arguments
     *
     * @return string
     */
    public function redirectUri(array $args): string
    {
        return $this->router->pathFor('objectslend_statuses');
    }

    /**
     * Get form URI
     *
     * @param array $args Route arguments
     *
     * @return string
     */
    public function formUri(array $args): string
    {
        return $this->router->pathFor(
            'objectslend_doremove_status',
            $args
        );
    }

    /**
     * Get confirmation removal page title
     *
     * @param array $args Route arguments
     *
     * @return string
     */
    public function confirmRemoveTitle(array $args): string
    {
        $status = new LendStatus($this->zdb, (int)$args['id']);
        return sprintf(
            _T('Remove status %1$s', 'objectslend'),
            $status->status_text
        );
    }

    /**
     * Remove object
     *
     * @param array $args Route arguments
     * @param array $post POST values
     *
     * @return boolean
     */
    protected function doDelete(array $args, array $post): bool
    {
        $status = new LendStatus($this->zdb, (int)$args['id']);
        return $status->delete();
    }

    // /CRUD - Delete
    // /CRUD
}
