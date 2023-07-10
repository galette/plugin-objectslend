<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Categories controller
 *
 * PHP version 5
 *
 * Copyright © 2021-2023 The Galette Team
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
 * @copyright 2021-2023 The Galette Team
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL License 3.0 or (at your option) any later version
 * @link      http://galette.tuxfamily.org
 * @since     2021-05-09
 */

namespace GaletteObjectsLend\Controllers\Crud;

use Analog\Analog;
use DI\Attribute\Inject;
use GaletteObjectsLend\Filters\CategoriesList;
use GaletteObjectsLend\Repository\Categories;
use GaletteObjectsLend\Entity\LendCategory;
use GaletteObjectsLend\Entity\Preferences;
use Galette\Controllers\Crud\AbstractPluginController;
use Slim\Psr7\Request;
use Slim\Psr7\Response;

/**
 * Categories controller
 *
 * @category  Controllers
 * @name      CategoriesController
 * @package   GaletteObjectsLend
 * @author    Johan Cwiklinski <johan@x-tnd.be>
 * @copyright 2021-2023 The Galette Team
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL License 3.0 or (at your option) any later version
 * @link      http://galette.tuxfamily.org
 * @since     2021-05-09
 */

class CategoriesController extends AbstractPluginController
{
    /**
     * @var array
     */
    #[Inject("Plugin Galette Objects Lend")]
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
        if (isset($this->session->objectslend_filter_categories)) {
            $filters = $this->session->objectslend_filter_categories;
        } else {
            $filters = new CategoriesList();
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

        $categories = new Categories($this->zdb, $this->login, $this->plugins, $filters);
        $list = $categories->getCategoriesList(true);

        $this->session->objectslend_filter_categories = $filters;

        //assign pagination variables to the template and add pagination links
        $filters->setViewPagination($this->routeparser, $this->view, false);

        $lendsprefs = new Preferences($this->zdb);
        // display page
        $this->view->render(
            $response,
            $this->getTemplate('categories_list'),
            array(
                'page_title'            => _T("Categories list", "objectslend"),
                'require_dialog'        => true,
                'categories'            => $list,
                'nb_categories'         => count($list),
                'filters'               => $filters,
                'olendsprefs'           => $lendsprefs,
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
        if (isset($this->session->objectslend_filter_categories)) {
            $filters = $this->session->objectslend_filter_categories;
        } else {
            $filters = new CategoriesList();
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
            //number of rows to show
            if (isset($post['nbshow'])) {
                $filters->show = $post['nbshow'];
            }
        }

        $this->session->objectslend_filter_categories = $filters;

        return $response
            ->withStatus(301)
            ->withHeader('Location', $this->routeparser->urlFor('objectslend_categories'));
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
        if ($this->session->objectslend_category !== null) {
            $category = $this->session->objectslend_category;
            $this->session->objectslend_category = null;
        } else {
            $category = new LendCategory($this->zdb, $this->plugins, $id);
        }

        if ($category->category_id !== null) {
            $title = _T("Edit category", "objectslend");
        } else {
            $title = _T("New category", "objectslend");
        }

        $lendsprefs = new Preferences($this->zdb);
        $params = [
            'page_title'    => $title,
            'category'      => $category,
            'time'          => time(),
            'action'        => $action,
            'olendsprefs'   => $lendsprefs
        ];

        // display page
        $this->view->render(
            $response,
            $this->getTemplate('category_edit'),
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
        $category = new LendCategory($this->zdb, $this->plugins, $id);
        $error_detected = [];

        $category->name = $post['name'];
        $category->is_active = $post['is_active'] == true;
        if ($category->store()) {
            // picture upload
            if (isset($_FILES['picture'])) {
                if ($_FILES['picture']['error'] === UPLOAD_ERR_OK) {
                    if ($_FILES['picture']['tmp_name'] != '') {
                        if (is_uploaded_file($_FILES['picture']['tmp_name'])) {
                            $res = $category->picture->store($_FILES['picture']);
                            if ($res < 0) {
                                $error_detected[] = $category->picture->getErrorMessage($res);
                            }
                        }
                    }
                } elseif ($_FILES['picture']['error'] !== UPLOAD_ERR_NO_FILE) {
                    Analog::log(
                        $category->picture->getPhpErrorMessage($_FILES['picture']['error']),
                        Analog::WARNING
                    );
                    $error_detected[] = $category->picture->getPhpErrorMessage(
                        $_FILES['picture']['error']
                    );
                }
            }

            if (isset($post['del_picture'])) {
                if (!$category->picture->delete($category->category_id)) {
                    $error_detected[] = _T("Delete failed", "objectslend");
                    Analog::log(
                        'Unable to delete picture for category ' . $category->name,
                        Analog::ERROR
                    );
                }
            }
        } else {
            $error_detected[] = _T("An error occured while storing the category.", "objectslend");
        }

        $args = ($id === null ? [] : ['id' => $id]);
        if (count($error_detected)) {
            $this->session->objectslend_category = $category;
            foreach ($error_detected as $error) {
                $this->flash->addMessage(
                    'error_detected',
                    $error
                );
            }

            return $response
                ->withStatus(301)
                ->withHeader(
                    'Location',
                    $this->routeparser->urlFor('objectslend_category', $args)
                );
        } else {
            //redirect to categories list
            $this->flash->addMessage(
                'success_detected',
                _T("Category has been saved", "objectslend")
            );

            return $response
                ->withStatus(301)
                ->withHeader(
                    'Location',
                    $this->routeparser->urlFor('objectslend_categories', $args)
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
        return $this->routeparser->urlFor('objectslend_categories');
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
        return $this->routeparser->urlFor(
            'objectslend_doremove_category',
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
        $category = new LendCategory($this->zdb, $this->plugins, (int)$args['id']);
        return sprintf(
            _T('Remove category %1$s', 'objectslend'),
            $category->name
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
        $category = new LendCategory($this->zdb, $this->plugins, (int)$args['id']);
        return $category->delete();
    }

    // /CRUD - Delete
    // /CRUD
}
