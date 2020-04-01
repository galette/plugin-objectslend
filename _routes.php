<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * ObjectsLend routes
 *
 * PHP version 5
 *
 * Copyright © 2017-2020 The Galette Team
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
 * @category Plugins
 * @package  GaletteObjectsLend
 *
 * @author    Johan Cwiklinski <johan@x-tnd.be>
 * @copyright 2017-2020 The Galette Team
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL License 3.0 or (at your option) any later version
 * @version   SVN: $Id$
 * @link      http://galette.tuxfamily.org
 * @since     2017-11-19
 */

use Analog\Analog;

use Galette\Entity\ContributionsTypes;
use Galette\Entity\Adherent;
use Galette\Entity\Texts;
use Galette\Entity\Contribution;

use Galette\Repository\Members;
use Galette\Repository\Contributions;

use GaletteObjectsLend\Entity\Preferences;
use GaletteObjectsLend\Entity\ObjectPicture;
use GaletteObjectsLend\Entity\Picture;
use GaletteObjectsLend\Entity\CategoryPicture;
use GaletteObjectsLend\Entity\LendCategory;
use GaletteObjectsLend\Entity\LendStatus;
use GaletteObjectsLend\Entity\LendObject;
use GaletteObjectsLend\Entity\LendRent;

use GaletteObjectsLend\Repository\Categories;
use GaletteObjectsLend\Repository\Objects;
use GaletteObjectsLend\Repository\Status;

use GaletteObjectsLend\Filters\StatusList;
use GaletteObjectsLend\Filters\ObjectsList;
use GaletteObjectsLend\Filters\CategoriesList;

//Constants and classes from plugin
require_once $module['root'] . '/_config.inc.php';

$this->get(
    '/preferences',
    function ($request, $response) use ($module, $module_id) {
        if ($this->session->objectslend_preferences !== null) {
            $lendsprefs = $this->session->objectslend_preferences;
            $this->session->objectslend_preferences = null;
        } else {
            $lendsprefs = new Preferences($this->zdb);
        }

        $ctypes = new ContributionsTypes($this->zdb);

        $params = [
            'page_title'    => _T('ObjectsLend preferences', 'objectslend'),
            'ctypes'        => $ctypes->getList(),
            'lendsprefs'    => $lendsprefs->getpreferences()
        ];

        // display page
        $this->view->render(
            $response,
            'file:[' . $module['route'] . ']preferences.tpl',
            $params
        );
        return $response;
    }
)->setName('objectslend_preferences')->add($authenticate);

$this->post(
    '/preferences',
    function ($request, $response) {
        $post = $request->getParsedBody();
        $lendsprefs = new Preferences($this->zdb);

        $error_detected = [];
        $success_detected = [];
        if ($lendsprefs->store($post, $error_detected)) {
            $this->flash->addMessage(
                'success_detected',
                _T('Preferences have been successfully stored!', 'objectslend')
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
                $this->router->pathFor('objectslend_preferences')
            );
    }
)->setName('store_objectlend_preferences')->add($authenticate);

$this->get(
    '/administration/images',
    function ($request, $response) use ($module, $module_id) {
        // display page
        $this->view->render(
            $response,
            'file:[' . $module['route'] . ']admin_picture.tpl',
            [
                'page_title' => _T('Pictures administration', 'objectslend')
            ]
        );
        return $response;
    }
)->setName('objectslend_adminimages')->add($authenticate);

$this->post(
    '/administration/images',
    function ($request, $response, $args) use ($module, $module_id) {
        $post = $request->getParsedBody();
        $success_detected = [];
        $error_detected = [];

        if (isset($post['save_categories']) || isset($post['save_objects'])) {
            $pic_class = isset($post['save_categories']) ? 'CategoryPicture' : 'ObjectPicture';
            $pic_class = '\GaletteObjectsLend\Entity\\' . $pic_class;
            $picture = new $pic_class($this->plugins);

            $zip_file = GALETTE_EXPORTS_PATH . 'objectslends/';
            if (!file_exists($zip_file)) {
                if (!mkdir($zip_file, 0755, true)) {
                    Analog::log(
                        'Unable to create backup dir `' . $zip_file . '`.',
                        Analog::ERROR
                    );
                    $error_detected[] = str_replace(
                        '%dir',
                        $zip_file,
                        _T('Unable to create backup directory `%dir`', 'objectslend')
                    );
                } else {
                    Analog::log(
                        'New directory `' . $zip_file . '` has been created',
                        Analog::INFO
                    );
                }
            }

            if (!count($error_detected)) {
                $zip_filename = isset($post['save_categories']) ? 'categories.zip' : 'objects.zip';
                $zip_file .= $zip_filename;

                $zip = new \ZipArchive();

                $ZIP_ERROR = [
                    ZipArchive::ER_EXISTS   => _T('File already exists.', 'objectslend'),
                    ZipArchive::ER_INCONS   => _T('Zip archive inconsistent.', 'objectslend'),
                    ZipArchive::ER_INVAL    => _T('Invalid argument.', 'objectslend'),
                    ZipArchive::ER_MEMORY   => _T('Memory allocation failure.', 'objectslend'),
                    ZipArchive::ER_NOENT    => _T('No such file.', 'objectslend'),
                    ZipArchive::ER_NOZIP    => _T('Not a zip archive.', 'objectslend'),
                    ZipArchive::ER_OPEN     => _T('Cannot open file.', 'objectslend'),
                    ZipArchive::ER_READ     => _T('Read error.', 'objectslend'),
                    ZipArchive::ER_SEEK     => _T('Seek error.', 'objectslend'),
                ];

                $result_code = $zip->open($zip_file, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
                if ($result_code !== true) {
                    $error_detected[] = isset($ZIP_ERROR[$result_code]) ?
                    $ZIP_ERROR[$result_code] :
                    _T('Unknown error.', 'objectslend');
                } else {
                    $dir_pictures = opendir($picture->getDir());
                    while (($file = readdir($dir_pictures)) !== false) {
                        if (preg_match('/^[0-9]+$/', pathinfo($file, PATHINFO_FILENAME)) !== false && !is_dir($file)) {
                            $zip->addFile($picture->getDir() . '/' . $file, $file);
                        }
                    }
                    $zip->close();
                    if (file_exists($zip_file)) {
                        header('Content-Type: application/zip');
                        header('Content-Disposition: attachment; filename="' . $zip_filename . '";');
                        header('Pragma: no-cache');
                        readfile($zip_file);
                    } else {
                        Analog::log(
                            'A request has been made to get file named `' .
                            $zip_filename .'` that does not exists.',
                            Analog::WARNING
                        );
                        $error_detected[] = str_replace(
                            '%filename',
                            $zip_filename,
                            _T('File %filename does not exists', 'objectslend')
                        );
                    }
                }
            }
        }

        if (isset($post['restore_objects'])) {
            $p = new ObjectPicture($this->plugins, -1);
            $p->restorePictures($success_detected, $error_detected);
        }

        if (isset($post['restore_categories'])) {
            $p = new CategoryPicture($this->plugins, -1);
            $p->restorePictures($success_detected, $error_detected);
        }

        foreach ($error_detected as $error) {
            $this->flash->addMessage(
                'error_detected',
                $error
            );
        }

        foreach ($success_detected as $success) {
            $this->flash->addMessage(
                'success_detected',
                $success
            );
        }

        return $response
        ->withStatus(301)
        ->withHeader(
            'Location',
            $this->router->pathFor('objectslend_adminimages')
        );
    }
)->setName('objectslend_adminimages_action')->add($authenticate);

$this->get(
    '/category/{action:edit|add}[/{id:\d+}]',
    function ($request, $response, $args) use ($module, $module_id) {
        $action = $args['action'];
        if ($action === 'edit' && !isset($args['id'])) {
            throw new \RuntimeException(
                _T('Category ID cannot be null calling edit route!')
            );
        } elseif ($action === 'add' && isset($args['id'])) {
            return $response
                ->withStatus(301)
                ->withHeader(
                    'Location',
                    $this->router->pathFor('objectslend_category', ['action' => 'add'])
                );
        }

        if ($this->session->objectslend_category !== null) {
            $category = $this->session->objectslend_category;
            $this->session->objectslend_category = null;
        } else {
            $category = new LendCategory($this->zdb, $this->plugins, isset($args['id']) ? (int)$args['id'] : null);
        }

        if ($category->category_id !== null) {
            $title = _T('Edit category', 'objectslend');
        } else {
            $title = _T('New category', 'objectslend');
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
            'file:[' . $module['route'] . ']category_edit.tpl',
            $params
        );
        return $response;
    }
)->setName('objectslend_category')->add($authenticate);

$this->post(
    '/category/{action:edit|add}[/{id:\d+}]',
    function ($request, $response, $args) use ($module, $module_id) {
        $action = $args['action'];
        $post = $request->getParsedBody();
        $category = new LendCategory($this->zdb, $this->plugins, isset($args['id']) ? (int)$args['id'] : null);
        $error_detected = [];

        /**
        * Store changes
        */
        $category->name = $post['name'];
        $category->is_active = $post['is_active'] == 'true';
        if ($category->store()) {
        // picture upload
            if (isset($_FILES['picture'])) {
                if ($_FILES['picture']['error'] === UPLOAD_ERR_OK) {
                    if ($_FILES['picture']['tmp_name'] !='') {
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
                    $error_detected[] = _T('Delete failed', 'objectslend');
                    Analog::log(
                        'Unable to delete picture for category ' . $category->name,
                        Analog::ERROR
                    );
                }
            }
        } else {
            $error_detected[] = _T('An error occured while storing the category.', 'objectslend');
        }

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
                    $this->router->pathFor('objectslend_category', $args)
                );
        } else {
        //redirect to categories list
            $this->flash->addMessage(
                'success_detected',
                _T('Category has been saved', 'objectslend')
            );

            return $response
                ->withStatus(301)
                ->withHeader(
                    'Location',
                    $this->router->pathFor('objectslend_categories', $args)
                );
        }
    }
)->setName('objectslend_category_action')->add($authenticate);

$this->get(
    '/{type:category|object}/{mode:photo|thumbnail}[/{id:\d+}]',
    function ($request, $response, $args) {
        $id = isset($args['id']) ? $args['id'] : '';
        $type = $args['type'];
        $class = '\GaletteObjectsLend\Entity\\' .
            ($type == 'category' ? 'CategoryPicture' : 'ObjectPicture');
        $picture = new $class($this->plugins, $id);

        $lendsprefs = new Preferences($this->zdb);
        $thumb = false;
        if (!$lendsprefs->showFullsize() || $args['mode'] == 'thumbnail') {
            //force thumbnail display from preferences
            $thumb = true;
        }

        if ($thumb) {
            $picture->displayThumb($lendsprefs);
        } else {
            $picture->display();
        }
    }
)->setName('objectslend_photo');

$this->get(
    '/categories[/{option:page|order}/{value:\d+}]',
    function ($request, $response, $args) use ($module, $module_id) {
        $option = null;
        if (isset($args['option'])) {
            $option = $args['option'];
        }
        $value = null;
        if (isset($args['value'])) {
            $value = $args['value'];
        }
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
        $categories_list = $categories->getCategoriesList(true);
        $this->session->objectslend_filter_categories = $filters;
        //assign pagination variables to the template and add pagination links
        $filters->setSmartyPagination($this->router, $this->view->getSmarty(), false);

        $lendsprefs = new Preferences($this->zdb);
        $title = _T('Categories list', 'objectslend');
        // display page
        $this->view->render(
            $response,
            'file:[' . $module['route'] . ']categories_list.tpl',
            array(
                'page_title'            => $title,
                'require_dialog'        => true,
                'categories'            => $categories_list,
                'filters'               => $filters,
                'olendsprefs'           => $lendsprefs,
                'time'                  => time()
            )
        );
        return $response;
    }
)->setName('objectslend_categories')->add($authenticate);

//categories list filtering
$this->post(
    '/categorie/filter',
    function ($request, $response) {
        $post = $request->getParsedBody();
        if (isset($this->session->objectslend_filter_categories)) {
            $filters = $this->session->objectslend_filter_categories;
        } else {
            $filters = new CategoriesList();
        }

        //reintialize filters
        if (isset($post['clear_filter'])) {
            $filters = new CategoriesList();
            //$filters->reinit();
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
                } else {
                    $filters->active_filter = 0;
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
            ->withHeader('Location', $this->router->pathFor('objectslend_categories'));
    }
)->setName('objectslend_filter_categories')->add($authenticate);

$this->get(
    '/category/remove/{id:\d+}',
    function ($request, $response, $args) {
        $category = new LendCategory($this->zdb, $this->plugins, (int)$args['id']);

        $data = [
            'id'            => $args['id'],
            'redirect_uri'  => $this->router->pathFor('objectslend_categories')
        ];

        // display page
        $this->view->render(
            $response,
            'confirm_removal.tpl',
            array(
                'type'          => _T('Category', 'objectslend'),
                'mode'          => $request->isXhr() ? 'ajax' : '',
                'page_title'    => sprintf(
                    _T('Remove category %1$s', 'objectslend'),
                    $category->name
                ),
                'form_url'      => $this->router->pathFor(
                    'objectslend_doremove_category',
                    ['id' => $category->category_id]
                ),
                'cancel_uri'    => $this->router->pathFor('objectslend_categories'),
                'data'          => $data
            )
        );
        return $response;
    }
)->setName('objectslend_remove_category')->add($authenticate);

$this->post(
    '/category/remove/{id:\d+}',
    function ($request, $response, $args) {
        $post = $request->getParsedBody();
        $ajax = isset($post['ajax']) && $post['ajax'] === 'true';
        $success = false;

        $uri = isset($post['redirect_uri']) ?
        $post['redirect_uri'] :
        $this->router->pathFor('slash');

        if (!isset($post['confirm'])) {
            $this->flash->addMessage(
                'error_detected',
                _T('Removal has not been confirmed!')
            );
        } else {
            $category = new LendCategory($this->zdb, $this->plugins, (int)$args['id']);
            $del = $category->delete();

            if ($del !== true) {
                $error_detected = str_replace(
                    '%category',
                    $category->name,
                    _T('An error occured trying to remove category %category :/')
                );

                $this->flash->addMessage(
                    'error_detected',
                    $error_detected
                );
            } else {
                $success_detected = str_replace(
                    '%category',
                    $category->name,
                    _T('Category %category has been successfully deleted.')
                );

                $this->flash->addMessage(
                    'success_detected',
                    $success_detected
                );

                $success = true;
            }
        }

        if (!$ajax) {
            return $response
                ->withStatus(301)
                ->withHeader('Location', $uri);
        } else {
            return $response->withJson(
                [
                    'success'   => $success
                ]
            );
        }
    }
)->setName('objectslend_doremove_category')->add($authenticate);

$this->get(
    '/status/{action:edit|add}[/{id:\d+}]',
    function ($request, $response, $args) use ($module, $module_id) {
        $action = $args['action'];
        if ($action === 'edit' && !isset($args['id'])) {
            throw new \RuntimeException(
                _T('Status ID cannot be null calling edit route!')
            );
        } elseif ($action === 'add' && isset($args['id'])) {
            return $response
                ->withStatus(301)
                ->withHeader(
                    'Location',
                    $this->router->pathFor('objectslend_status', ['action' => 'add'])
                );
        }

        if ($this->session->objectslend_status !== null) {
            $status = $this->session->objectslend_status;
            $this->session->objectslend_status = null;
        } else {
            $status = new LendStatus($this->zdb, isset($args['id']) ? (int)$args['id'] : null);
        }

        if ($status->status_id !== null) {
            $title = str_replace(
                '%status',
                $status->status_text,
                _T('Edit status %status', 'objectslend')
            );
        } else {
            $title = _T('New status', 'objectslend');
        }

        $params = [
            'page_title'    => $title,
            'status'        => $status,
            'action'        => $action
        ];

        // display page
        $this->view->render(
            $response,
            'file:[' . $module['route'] . ']status_edit.tpl',
            $params
        );
        return $response;
    }
)->setName('objectslend_status')->add($authenticate);

$this->post(
    '/status/{action:edit|add}[/{id:\d+}]',
    function ($request, $response, $args) use ($module, $module_id) {
        $action = $args['action'];
        $post = $request->getParsedBody();
        $status = new LendStatus($this->zdb, isset($args['id']) ? (int)$args['id'] : null);
        $error_detected = [];

        $status->status_text = $post['text'];
        $status->is_home_location = isset($post['is_home_location']);
        $status->is_active = isset($post['is_active']);
        $days = trim($post['rent_day_number']);
        $status->rent_day_number = strlen($days) > 0 ? intval($days) : null;
        if (!$status->store()) {
            $error_detected[] = _T('An error occured while storing the status.', 'objectslend');
        }

        if (count($error_detected)) {
            $this->session->objectslend_status = $status;
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
                    $this->router->pathFor('objectslend_status', $args)
                );
        } else {
        //redirect to categories list
            $this->flash->addMessage(
                'success_detected',
                _T('Status has been saved', 'objectslend')
            );

            return $response
                ->withStatus(301)
                ->withHeader(
                    'Location',
                    $this->router->pathFor('objectslend_statuses', $args)
                );
        }
    }
)->setName('objectslend_status_action')->add($authenticate);

$this->get(
    '/statuses[/{option:page|order}/{value:\d+}]',
    function ($request, $response, $args) use ($module, $module_id) {
        $option = null;
        if (isset($args['option'])) {
            $option = $args['option'];
        }
        $value = null;
        if (isset($args['value'])) {
            $value = $args['value'];
        }

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

        if (count(LendStatus::getActiveHomeStatuses($this->zdb)) == 0) {
            $this->flash->addMessage(
                'error_detected',
                _T('You should add at last 1 status \"on site\" to ensure the plugin works well!', 'objectslend')
            );
        }
        if (count(LendStatus::getActiveTakeAwayStatuses($this->zdb)) == 0) {
            $this->flash->addMessage(
                'error_detected',
                _T(
                    'You should add at last 1 status \"object borrowed\" to ensure the plugin works well!',
                    'objectslend'
                )
            );
        }

        $this->session->objectslend_filter_statuses = $filters;

        //assign pagination variables to the template and add pagination links
        $filters->setSmartyPagination($this->router, $this->view->getSmarty(), false);

        $lendsprefs = new Preferences($this->zdb);
        // display page
        $this->view->render(
            $response,
            'file:[' . $module['route'] . ']status_list.tpl',
            array(
                'page_title'            => _T('Status list', 'objectslend'),
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
)->setName('objectslend_statuses')->add($authenticate);

//status list filtering
$this->post(
    '/statuses/filter',
    function ($request, $response) {
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
)->setName('objectslend_filter_statuses')->add($authenticate);

$this->get(
    '/status/remove/{id:\d+}',
    function ($request, $response, $args) {
        $status = new LendStatus($this->zdb, (int)$args['id']);

        $data = [
            'id'            => $args['id'],
            'redirect_uri'  => $this->router->pathFor('objectslend_statuses')
        ];

        // display page
        $this->view->render(
            $response,
            'confirm_removal.tpl',
            array(
                'type'          => _T('Status', 'objectslend'),
                'mode'          => $request->isXhr() ? 'ajax' : '',
                'page_title'    => sprintf(
                    _T('Remove status %1$s', 'objectslend'),
                    $status->status_text
                ),
                'form_url'      => $this->router->pathFor(
                    'objectslend_doremove_status',
                    ['id' => $status->status_id]
                ),
                'cancel_uri'    => $this->router->pathFor('objectslend_statuses'),
                'data'          => $data
            )
        );
        return $response;
    }
)->setName('objectslend_remove_status')->add($authenticate);

$this->post(
    '/status/remove/{id:\d+}',
    function ($request, $response, $args) {
        $post = $request->getParsedBody();
        $ajax = isset($post['ajax']) && $post['ajax'] === 'true';
        $success = false;

        $uri = isset($post['redirect_uri']) ?
        $post['redirect_uri'] :
        $this->router->pathFor('slash');

        if (!isset($post['confirm'])) {
            $this->flash->addMessage(
                'error_detected',
                _T('Removal has not been confirmed!')
            );
        } else {
            $status = new LendStatus($this->zdb, (int)$args['id']);
            $del = $status->delete();

            if ($del !== true) {
                $error_detected = str_replace(
                    '%status',
                    $status->status_text,
                    _T('An error occured trying to remove status %status :/')
                );

                $this->flash->addMessage(
                    'error_detected',
                    $error_detected
                );
            } else {
                $success_detected = str_replace(
                    '%status',
                    $status->status_text,
                    _T('Status %status has been successfully deleted.')
                );

                $this->flash->addMessage(
                    'success_detected',
                    $success_detected
                );

                $success = true;
            }
        }

        if (!$ajax) {
            return $response
                ->withStatus(301)
                ->withHeader('Location', $uri);
        } else {
            return $response->withJson(
                [
                    'success'   => $success
                ]
            );
        }
    }
)->setName('objectslend_doremove_status')->add($authenticate);

$this->get(
    '/object/{action:edit|add}[/{id:\d+}]',
    function ($request, $response, $args) use ($module, $module_id) {
        $option = null;
        $action = $args['action'];
        if ($action === 'edit' && !isset($args['id'])) {
            throw new \RuntimeException(
                _T('Object ID cannot be null calling edit route!')
            );
        } elseif ($action === 'add' && isset($args['id'])) {
            return $response
                ->withStatus(301)
                ->withHeader(
                    'Location',
                    $this->router->pathFor('objectslend_object', ['action' =>'add'])
                );
        }

        if ($this->session->objectslend_object !== null) {
            $object = $this->session->objectslend_object;
            $this->session->objectslend_object = null;
        } else {
            $object = new LendObject($this->zdb, $this->plugins, isset($args['id']) ? (int)$args['id'] : null);
        }
        $rents=LendRent::getRentsForObjectId($args['id']);
        $categories = new Categories($this->zdb, $this->login, $this->plugins);
        $categories_list = $categories->getCategoriesList(true);

        if ($object->object_id !== null) {
            $title = _T('Edit object', 'objectslend');
        } else {
            $title = _T('New object', 'objectslend');
        }
        // Modif préconisée par trasher
        $sfilter = new StatusList();
        $sfilter->active_filter = \GaletteObjectsLend\Repository\Status::ACTIVE;
        $statuses = new Status($this->zdb, $this->login, $sfilter);

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
        $statuses = new Status($this->zdb, $this->login, $sfilter);
        $slist = $statuses->getStatusList(true);

        $lendsprefs = new Preferences($this->zdb);
        $params = [
            'page_title'    => $title,
            'object'        => $object,
            'rents'         => $rents,
            'time'          => time(),
            'action'        => $action,
            'lendsprefs'    => $lendsprefs->getpreferences(),
            'olendsprefs'   => $lendsprefs,
            'categories'    => $categories_list,
            'statuses'      => $slist
        ];

        // display page
        $this->view->render(
            $response,
            'file:[' . $module['route'] . ']objects_edit.tpl',
            $params
        );
        return $response;
    }
)->setName('objectslend_object')->add($authenticate);

$this->get(
    '/object/clone/{id:\d+}',
    function ($request, $response, $args) use ($module, $module_id) {
        $object = new LendObject($this->zdb, $this->plugins, (int)$args['id']);

        if ($object->clone()) {
            $this->flash->addMessage(
                'success_detected',
                str_replace(
                    '%id',
                    $args['id'],
                    _T('Successfully cloned from #%id.<br/>You can now edit it.', 'objectslend')
                )
            );
        } else {
            $this->flash->addMessage(
                'error_detected',
                _T('An error occured cloning object :(', 'objectslend')
            );
        }

        return $response
            ->withStatus(301)
            ->withHeader(
                'Location',
                $this->router->pathFor(
                    'objectslend_object',
                    [
                    'action'    => 'edit',
                    'id'        => $object->object_id
                    ]
                )
            );
    }
)->setName('objectslend_object_clone')->add($authenticate);

$this->post(
    '/object/{action:edit|add}[/{id:\d+}]',
    function ($request, $response, $args) use ($module, $module_id) {
        $action = $args['action'];
        $post = $request->getParsedBody();

        $object = new LendObject($this->zdb, $this->plugins, isset($args['id']) ? (int)$args['id'] : null);
        $error_detected = [];

        $object->name = $post['name'];
        $object->description = $post['description'];
        //TODO: check if category do exits?
        $object->category_id = empty($post['category_id']) ? null : $post['category_id'];
        $object->serial_number = $post['serial'];
        if ($post['price'] != '') {
            //FIXME: better currency format handler
            $object->price = str_replace(' ', '', str_replace(',', '.', $post['price']));
        }
        if ($post['rent_price'] != '') {
            //FIXME: better currency format handler
            $object->rent_price = str_replace(' ', '', str_replace(',', '.', $post['rent_price']));
        }
        $object->price_per_day = $post['price_per_day'] == 'true';
        $object->dimension = $post['dimension'];
        if ($post['weight'] != '') {
            //FIXME: better format handler
            $object->weight = str_replace(' ', '', str_replace(',', '.', $post['weight']));
        }
        $object->is_active = $post['is_active'] == 'true';

        if ($object->store()) {
            $success_detected[] = _T('Object has been successfully stored!', 'objectslend');
            if (isset($pot['1st_status'])) {
                $rent = new LendRent();
                $rent->object_id = $object->object_id;
                $rent->status_id = $post['1st_status'];
                $rent->store();
            }

            $object_id = $object->object_id;

            // Change status
            if ($post['status']) {
                LendRent::closeAllRentsForObject(intval($object_id), $post['new_comment']);

                $rent = new LendRent();
                $rent->object_id = $object_id;
                $rent->status_id = $post['new_status'];
                if (filter_input(INPUT_POST, 'new_adh') != 'null') {
                    $rent->adherent_id = $post['new_adh'];
                }
                $rent->store();
            }
             // picture upload
            if (isset($_FILES['picture'])) {
                if ($_FILES['picture']['error'] === UPLOAD_ERR_OK) {
                    if ($_FILES['picture']['tmp_name'] !='') {
                        if (is_uploaded_file($_FILES['picture']['tmp_name'])) {
                            $res = $object->picture->store($_FILES['picture']);
                            if ($res < 0) {
                                $error_detected[] = $object->picture->getErrorMessage($res);
                            }
                        }
                    }
                } elseif ($_FILES['picture']['error'] !== UPLOAD_ERR_NO_FILE) {
                    Analog::log(
                        $object->picture->getPhpErrorMessage($_FILES['picture']['error']),
                        Analog::WARNING
                    );
                    $error_detected[] = $object->picture->getPhpErrorMessage(
                        $_FILES['picture']['error']
                    );
                }
            }

            if (isset($post['del_picture'])) {
                if (!$object->picture->delete($object->object_id)) {
                    $error_detected[] = _T('Delete failed', 'objectslend');
                    Analog::log(
                        'Unable to delete picture for object ' . $object->name,
                        Analog::ERROR
                    );
                }
            }
        } else {
            $error_detected[] = _T('Something went wrong saving object', 'objectslend');
        }

        if (count($error_detected)) {
            $this->session->objectslend_object = $object;
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
                    $this->router->pathFor('objectslend_object', $args)
                );
        } else {
        //redirect to objects list
            $this->flash->addMessage(
                'success_detected',
                _T('Object has been saved', 'objectslend')
            );

            return $response
                ->withStatus(301)
                ->withHeader(
                    'Location',
                    $this->router->pathFor('objectslend_objects', $args)
                );
        }
    }
)->setName('objectslend_object_action')->add($authenticate);

$this->get(
    '/objects[/{option:page|order|category}/{value:\d+}]',
    function ($request, $response, $args) use ($module, $module_id) {
        $option = null;
        if (isset($args['option'])) {
            $option = $args['option'];
        }
        $value = null;
        if (isset($args['value'])) {
            $value = $args['value'];
        }
        $filters = new ObjectsList();
        if (isset($this->session->objectslend_filter_objects)) {
            $filters = $this->session->objectslend_filter_objects;
        } else {
            $filters = new ObjectsList();
        }

        if ($option !== null) {
            switch ($option) {
                case 'page':
                    $filters->current_page = (int)$value;
                    break;
                case 'order':
                    $filters->orderby = $value;
                    break;
                case 'category':
                    if ($value == 0) {
                        $value = null;
                    }
                    $filters->category_filter = $value;
                    break;
            }
        }

        $lendsprefs = new Preferences($this->zdb);
        $objects = new Objects($this->zdb, $this->plugins, $lendsprefs, $filters);
        $list = $objects->getObjectsList(true);

        $this->session->objectslend_filter_objects = $filters;

        //assign pagination variables to the template and add pagination links
        $filters->setViewCommonsFilters($lendsprefs, $this->view->getSmarty());
        $filters->setSmartyPagination($this->router, $this->view->getSmarty(), false);

        $categories = new Categories($this->zdb, $this->login, $this->plugins);
        $categories_list = $categories->getCategoriesList(true);

        // display page
        $this->view->render(
            $response,
            'file:[' . $module['route'] . ']objects_list.tpl',
            array(
                'page_title'            => _T('Objects list', 'objectslend'),
                'require_dialog'        => true,
                'objects'               => $list,
                'nb_objects'            => count($list),
                'filters'               => $filters,
                'lendsprefs'            => $lendsprefs->getpreferences(),
                'olendsprefs'           => $lendsprefs,
                'time'                  => time(),
                'module_id'             => $module_id,
                'categories'            => $categories_list
            )
        );
        return $response;
    }
)->setName('objectslend_objects')->add($authenticate);

//objects list filtering
$this->post(
    '/objects/filter',
    function ($request, $response) {

        $post = $request->getParsedBody();
        if (isset($this->session->objectslend_filter_objects)) {
            $filters = $this->session->objectslend_filter_objects;
        } else {
            $filters = new ObjectsList();
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

        $this->session->objectslend_filter_objects = $filters;

        return $response
            ->withStatus(301)
            ->withHeader('Location', $this->router->pathFor('objectslend_objects'));
    }
)->setName('objectslend_filter_objects')->add($authenticate);

//Remove Objects
$this->get(
    '/object/remove[/{id:\d+}]',
    function ($request, $response, $args) {
        //$post = $request->getParsedBody();
        $object = new LendObject($this->zdb, $this->plugins, (int)$args['id']);
        if (!(int)$args['id']) {
            $filters =  $this->session->objectslend_filter_objects;
            $data = [
                'id'            => $filters->selected,
                'redirect_uri'  => $this->router->pathFor('objectslend_objects')
            ];
            // display page
            $this->view->render(
                $response,
                'confirm_removal.tpl',
                array(
                    'type'          => _T('Object', 'objectslend'),
                    'mode'          => $request->isXhr() ? 'ajax' : '',
                    'page_title'    => _T('Remove objects'),
                    'message'       => str_replace(
                        '%count',
                        count($data['id']),
                        _T('You are about to remove %count objects.')
                    ),
                    'form_url'      => $this->router->pathFor('objectslend_doremove_object'),
                    'cancel_uri'    => $this->router->pathFor('objectslend_objects'),
                    'data'          => $data
                )
            );
            return $response;
        } else {
            $data = [
                'id'            => $args['id'],
                'redirect_uri'  => $this->router->pathFor('objectslend_objects')
            ];
            // display page
            $this->view->render(
                $response,
                'confirm_removal.tpl',
                array(
                    'type'          => _T('Object', 'objectslend'),
                    'mode'          => $request->isXhr() ? 'ajax' : '',
                    'page_title'    => sprintf(
                        _T('Remove object %1$s', 'objectslend'),
                        $object->name
                    ),
                    'form_url'      => $this->router->pathFor(
                        'objectslend_doremove_object',
                        ['id' => $object->object_id]
                    ),
                    'cancel_uri'    => $this->router->pathFor('objectslend_objects'),
                    'data'          => $data
                )
            );
            return $response;
        }
    }
)->setName('objectslend_remove_object')->add($authenticate);

$this->post(
    '/object/doremove[/{id:\d+}]',
    function ($request, $response, $args) {
        $post = $request->getParsedBody();
        $ajax = isset($post['ajax']) && $post['ajax'] === 'true';
        $uri = isset($post['redirect_uri']) ?
        $post['redirect_uri'] :
        $this->router->pathFor('slash');

        if (!isset($post['confirm'])) {
            $this->flash->addMessage(
                'error_detected',
                _T('Removal has not been confirmed!')
            );
        } else {
            $success = true;
            $name="";
            if (is_array($post['id'])) {
                //delete multiple objects
                foreach ($post['id'] as $id) {
                    $name=  $id. ", " . $name ;
                    $object = new LendObject($this->zdb, $this->plugins, (int)$id);
                    $del = $object->delete();
                    if ($del !== $id) {
                        $success = false;
                    }
                }
            } else {
                //delete one  object
                $id=$args['id'];
                $name=$id;
                $object = new LendObject($this->zdb, $this->plugins, (int)$id);
                $del = $object->delete();
                if ($del !== $id) {
                    $success = false;
                }
            }

            if ($success == false) {
                $error_detected = str_replace(
                    '%name',
                    $name,
                    _T('An error occured trying to remove object %name :/')
                );
                $this->flash->addMessage(
                    'error_detected',
                    $error_detected
                );
            } else {
                $success_detected = str_replace(
                    '%name',
                    $name,
                    _T('Object %name has been successfully deleted.')
                );
                $this->flash->addMessage(
                    'success_detected',
                    $success_detected
                );

                $success = true;
            }
        }
        if (!$ajax) {
            return $response
                ->withStatus(301)
                ->withHeader('Location', $uri);
        } else {
            return $response->withJson(
                [
                    'success'   => $success
                ]
            );
        }
    }
)->setName('objectslend_doremove_object')->add($authenticate);

//Batch actions on objects list
$this->post(
    '/objects/batch',
    function ($request, $response) {
        $post = $request->getParsedBody();

        if (isset($post['object_ids'])) {
            if (isset($this->session->objectslend_filter_objects)) {
                $filters = $this->session->objectslend_filter_objects;
            } else {
                $filters = new ObjectsList();
            }

            $filters->selected = $post['object_ids'];
            $this->session->objectslend_filter_objects = $filters;

            if (isset($post['Delete'])) {
                return $response
                    ->withStatus(301)
                    ->withHeader('Location', $this->router->pathFor('objectslend_remove_object'));
            } elseif (isset($post['TakeAway'])) {
                return $response
                    ->withStatus(301)
                    ->withHeader('Location', $this->router->pathFor('objectslend_take_object'));
            } elseif (isset($post['GiveBack'])) {
                return $response
                    ->withStatus(301)
                    ->withHeader('Location', $this->router->pathFor('objectslend_give_object_back'));
            } elseif (isset($post['Disable'])) {
                return $response
                    ->withStatus(301)
                    ->withHeader('Location', $this->router->pathFor('objectslend_disable_objects'));
            } elseif (isset($post['Enable'])) {
                return $response
                    ->withStatus(301)
                    ->withHeader('Location', $this->router->pathFor('objectslend_enable_objects'));
            } elseif (isset($post['print_list'])) {
                return $response
                    ->withStatus(301)
                    ->withHeader('Location', $this->router->pathFor('objectslend_objects_print'));
            } elseif (isset($post['print_objects'])) {
                return $response
                    ->withStatus(301)
                    ->withHeader('Location', $this->router->pathFor('objectslend_objects_printobject'));
            } else {
                $this->flash->addMessage(
                    'error_detected',
                    _T('No action was found. Please contact plugin developpers.')
                );
            }
        } else {
            $this->flash->addMessage(
                'error_detected',
                _T('No object was selected, please check at least one.')
            );
        }
        return $response
            ->withStatus(301)
            ->withHeader('Location', $this->router->pathFor('objectslend_objects'));
    }
)->setName('objectslend_batch-objectslist')->add($authenticate);

$this->get(
    '/objects/print[/{id:\d+}]',
    function ($request, $response, $args) {
        $lendsprefs = new Preferences($this->zdb);

        if (isset($this->session->objectslend_filter_objects)) {
            $filters =  $this->session->objectslend_filter_objects;
        } else {
            $filters = new ObjectsList();
        }
        if (isset($args['id'])) {
            $sfilters = new ObjectsList();
            $id[]=$args['id'];
            $sfilters->selected = $id;
        } else {
            $sfilters = $filters;
        }

        $objects = new Objects($this->zdb, $this->plugins, $lendsprefs, $sfilters);

        $list = $objects->getObjectsList(true, null, true, false, true);

        $pdf = new GaletteObjectsLend\IO\PdfObjects(
            $this->zdb,
            $this->preferences,
            $lendsprefs,
            $filters,
            $this->login
        );

        $pdf->drawList($list);
        $pdf->Output(_T('Objects list', 'objectslend') . '.pdf', 'D');
    }
)->setName('objectslend_objects_print')->add($authenticate);

$this->get(
    '/objects/printobject[/{id:\d+}]',
    function ($request, $response, $args) {
        $lendsprefs = new Preferences($this->zdb);
        if (isset($this->session->objectslend_filter_objects)) {
            $filters =  $this->session->objectslend_filter_objects;
        } else {
            $filters = new ObjectsList();
        }
        if (isset($args['id'])) {
            $sfilters = new ObjectsList();
            $id[]=$args['id'];
            $sfilters->selected = $id;
        } else {
            $sfilters = $filters;
        }

        $objects = new Objects($this->zdb, $this->plugins, $lendsprefs, $sfilters);
        $list = $objects->getObjectsList(true, null, true, false, true);

        $pdf = new GaletteObjectsLend\IO\PdfObjects(
            $this->zdb,
            $this->preferences,
            $lendsprefs,
            $sfilters,
            $this->login
        );
        // a changer pour imprimer les locations
        $pdf->drawList($list);
        $pdf->drawList1($list);
        $pdf->Output(_T('Objects list', 'objectslend') . '.pdf', 'D');
    }
)->setName('objectslend_objects_printobject')->add($authenticate);

//  GiveBack Objects
$this->get(
    '/object/give_object_back[/{id:\d+}]',
    function ($request, $response, $args) use ($module, $module_id) {
        $option=null;
        $action = $args['action'];
        $title = _T('Give back object', 'objectslend');

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
        $slist = $statuses->getStatusList(true);
        $lendsprefs = new Preferences($this->zdb);
        $objects = new Objects($this->zdb, $this->plugins, $lendsprefs, null);
        $objects_list = $objects->getObjectsList(true, null, true, false);
        $object = new LendObject($this->zdb, $this->plugins, isset($args['id']) ? (int)$args['id'] :null, null);
        $categories = new Categories($this->zdb, $this->login, $this->plugins);
        $categories_list = $categories->getCategoriesList(true);

        // members
        $members = [];
        $m = new Members();
        $required_fields = array(
            'id_adh',
            'nom_adh',
            'prenom_adh'
        );
        $list_members = $m->getMembersList(false, $required_fields, true, false, false, false);

        if (count($list_members) > 0) {
            foreach ($list_members as $member) {
                $pk = Adherent::PK;
                $sname=Adherent::getNameWithCase($member->nom_adh, $member->prenom_adh);
                $members[$member->$pk] = $sname;
            }
        }

        if (!(int)$args['id']) {
            $filters =  $this->session->objectslend_filter_objects;
            $ids=$filters->selected;
        } else {
            $ids[]=$args['id'];
        }
        $params = [
            'require_calendar'  => true,
            'id'            => $ids,
            'page_title'    => $title,
            'members'       =>$members,
            'objects'       => $objects_list,
            'object'        => $object,
            'time'          => time(),
            'action'        => $action,
            'lendsprefs'    => $lendsprefs->getpreferences(),
            'olendsprefs'   => $lendsprefs,
            'categories'    => $categories_list,
            'statuses'      => $slist
        ];

        // display page
        $this->view->render(
            $response,
            'file:[' . $module['route'] . ']give_object_back.tpl',
            $params
        );
        return $response;
    }
)->setName('objectslend_give_object_back')->add($authenticate);

$this->post(
    '/do_giveback_lend',
    function ($request, $response, $args) use ($module, $module_id) {
        $ok = true;
        foreach ($_POST['ids'] as $object_id) {
            $comments = isset($_POST['comments']) ? $_POST['comments'] : 'OK';
            $del = LendRent::closeAllRentsForObject($object_id, $comments);
            if ($del == false) {
                $ok=false;
            }
        }

        if ($ok !== true) {
            $error_detected = $del;
            $this->flash->addMessage('error_detected', $error_detected);
            return $response
                ->withStatus(301)
                ->withHeader(
                    'Location',
                    $this->router->pathFor('objectslend_objects', $args)
                );
        } else {
            $success_detected = "OK";
            $this->flash->addMessage('success_detected', $success_detected);
            return $response
                ->withStatus(301)
                ->withHeader(
                    'Location',
                    $this->router->pathFor('objectslend_objects', $args)
                );
        }
    }
)->setName('objectslend_do_giveback_lend')->add($authenticate);

// Take Objects
$this->get(
    '/object/take_object[/{id:\d+}]',
    function ($request, $response, $args) use ($module, $module_id) {
        $option = null;
        $action = $args['action'];
        $title = _T('Take object', 'objectslend');

        if (isset($this->session->objectslend_filter_statuses)) {
            $filter = $this->session->objectslend_filter_statuses;
        } else {
            $filter = new StatusList();
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

        $statuses = new Status($this->zdb, $this->login, $filter);
        $slist = $statuses->getStatusList(true);
        $lendsprefs = new Preferences($this->zdb);
        $objects = new Objects($this->zdb, $this->plugins, $lendsprefs, null);
        $objects_list = $objects->getObjectsList(true, null, true, false);
        $object = new LendObject($this->zdb, $this->plugins, isset($args['id']) ? (int)$args['id'] :null, true);
        $categories = new Categories($this->zdb, $this->login, $this->plugins);
        $categories_list = $categories->getCategoriesList(true);

        $ct = new ContributionsTypes($this->zdb);
        $contributions_types = $ct->getList($args['type'] === 'fee');
        // members
        $members = [];
        $m = new Members();
        $required_fields = array(
            'id_adh',
            'nom_adh',
            'prenom_adh'
        );
        $list_members = $m->getMembersList(false, $required_fields, true, false, false, false);

        if (count($list_members) > 0) {
            foreach ($list_members as $member) {
                $pk = Adherent::PK;
                $sname=Adherent::getNameWithCase($member->nom_adh, $member->prenom_adh);
                $members[$member->$pk] = $sname;
            }
        }

        if (!(int)$args['id']) {
            $filters =  $this->session->objectslend_filter_objects;
            $ids=$filters->selected;
        } else {
            $ids[]=$args['id'];
        }

        $params = [
            'require_calendar'  => true,
            'id'            => $ids,
            'page_title'    => $title,
            'members'       =>$members,
            'objects'       => $objects_list,
            'object'        => $object,
            'time'          => time(),
            'action'        => $action,
            'lendsprefs'    => $lendsprefs->getpreferences(),
            'olendsprefs'   => $lendsprefs,
            'categories'    => $categories_list,
            'contribution'  => $contributions_types,
            'statuses'      => $slist
        ];

        // display page
        $this->view->render(
            $response,
            'file:[' . $module['route'] . ']take_object.tpl',
            $params
        );

        return $response;
    }
)->setName('objectslend_take_object')->add($authenticate);

$this->post(
    '/object/dotake_lend',
    function ($request, $response, $args) {
        $ok = true;
        $adherent_id = $_POST['adherent_id'];
        $payment_type = (int)$_POST['payment_type'];
        $date_begin = $_POST['date_begin'];
        $date_forecast = $_POST['date_forecast'];
        $status_id = $_POST['status_id'];
        $comments = $_POST['comments'];

        $ct = new ContributionsTypes($this->zdb);
        $contributions_types = $ct->getList($args['type'] === 'fee');
        $lendsprefs = new Preferences($this->zdb);
        foreach ($_POST['ids'] as $object_id) {
            $object = new LendObject($this->zdb, $this->plugins, (int)$object_id, true);
            $adherent_id=$_POST['adherent_id'];
            // ajouter test si objet pris, alors le clore
            $close=LendRent::closeAllRentsForObject($object_id, "Take by : " . $_POST['adherent_id']);
            $new=LendRent::newRentForObject(
                $object_id,
                $date_begin,
                $date_forecast,
                $status_id,
                $adherent_id,
                $comments
            );
            if ($new == false) {
                $ok=false;
            }

            // Récupération du prix de location
            $rentprice = str_replace(',', '.', $object->rent_price);
            // Ajout d'une contribution
            if ($rentprice > 0 && $lendsprefs->{Preferences::PARAM_AUTO_GENERATE_CONTRIBUTION}) {
                $contrib = new Contribution($this->zdb, $this->login);
                $info = str_replace(
                    array(
                        '{NAME}',
                        '{DESCRIPTION}',
                        '{SERIAL_NUMBER}',
                        '{PRICE}',
                        '{RENT_PRICE}',
                        '{WEIGHT}',
                        '{DIMENSION}'
                    ),
                    array(
                        $object->name,
                        $object->description,
                        $object->serial_number,
                        $object->price,
                        $object->rent_price,
                        $object->weight,
                        $object->dimension
                    ),
                    $lendsprefs->{Preferences::PARAM_GENERATED_CONTRIB_INFO_TEXT}
                );
                $values = array(
                    'id_adh' => $adherent_id ,
                    'id_type_cotis' =>  "4" ,
                    'montant_cotis' => "10",
                    'contribution_type' => $lendsprefs->{Preferences::PARAM_GENERATED_CONTRIBUTION_TYPE_ID},
                    'date_enreg' => date(_T('Y-m-d')),
                    'date_debut_cotis' => date(_T('Y-m-d')),
                    'type_paiement_cotis' => $payment_type,
                    'info_cotis' => $info
                );
                $contrib->check($values, array(), array());
                $contrib->store();
            }
        }
        $id_adh = (int)$_POST['adherent_id'];
        $member = new Adherent($this->zdb, $id_adh);
        if ($ok === false) {
            $error_detected = _T('Object lent has not been successfully stored!', 'objectslend');
            $this->flash->addMessage(
                'error_detected',
                $error_detected
            );
            return $response
                ->withStatus(301)
                ->withHeader(
                    'Location',
                    $this->router->pathFor('objectslend_objects')
                );
        } else {
            $success_detected =
            _T('Objectlent has been successfully stored', 'objectslend');
            $this->flash->addMessage(
                'success_detected',
                $success_detected
            );

            return $response
                ->withStatus(301)
                ->withHeader(
                    'Location',
                    $this->router->pathFor('objectslend_objects')
                );
        }
    }
)->setName('objectslend_do_take_lend')->add($authenticate);

// Disable Objects
$this->get(
    '/object/disable_objects',
    function ($request, $response, $args) {

        $filters =  $this->session->objectslend_filter_objects;
        $ids = $filters->selected;

        $lendsprefs = new Preferences($this->zdb);
        $objects = new Objects($this->zdb, $this->plugins, $lendsprefs, $filters);

        $disable = $objects->disableObjects($ids);
        if ($disable === false) {
            $error_detected = $disable;
            $this->flash->addMessage(
                'error_detected',
                $error_detected
            );
            return $response
                ->withStatus(301)
                ->withHeader(
                    'Location',
                    $this->router->pathFor('objectslend_objects')
                );
        } else {
            $success_detected =
            _T('Transaction has been successfully stored', 'objectslend');
            $this->flash->addMessage(
                'success_detected',
                $success_detected
            );

            return $response
                ->withStatus(301)
                ->withHeader(
                    'Location',
                    $this->router->pathFor('objectslend_objects')
                );
        }
    }
)->setName('objectslend_disable_objects')->add($authenticate);

// Enable Objects
$this->get(
    '/object/enable_objects',
    function ($request, $response, $args) {

        $filters =  $this->session->objectslend_filter_objects;
        $ids = $filters->selected;

        $lendsprefs = new Preferences($this->zdb);
        $objects = new Objects($this->zdb, $this->plugins, $lendsprefs, $filters);

        $enable = $objects->enableObjects($ids);
        if ($enable === false) {
            $error_detected = $enable;
            $this->flash->addMessage(
                'error_detected',
                $error_detected
            );
            return $response
                ->withStatus(301)
                ->withHeader(
                    'Location',
                    $this->router->pathFor('objectslend_objects')
                );
        } else {
            $success_detected =
            _T('Transaction has been successfully stored', 'objectslend');
            $this->flash->addMessage(
                'success_detected',
                $success_detected
            );

            return $response
                ->withStatus(301)
                ->withHeader(
                    'Location',
                    $this->router->pathFor('objectslend_objects')
                );
        }
    }
)->setName('objectslend_enable_objects')->add($authenticate);

// Show all rents
$this->get(
    '/object/show/{id:\d+}',
    function ($request, $response, $args) use ($module, $module_id) {

        $option = null;

        $object = new LendObject($this->zdb, $this->plugins, isset($args['id']) ? (int)$args['id'] : null);
        $rents = LendRent::getRentsForObjectId($args['id'], false, 'rent_id desc');
        $categories = new Categories($this->zdb, $this->login, $this->plugins);
        $categories_list = $categories->getCategoriesList(true);

        if ($object->object_id !== null) {
            $title = _T('List rents', 'objectslend');
        }

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
        $slist = $statuses->getStatusList(true);

        $lendsprefs = new Preferences($this->zdb);
        $params = [
            'page_title'    => $title,
            'object'        => $object,
            'rents'         => $rents,
            'time'          => time(),
            'lendsprefs'    => $lendsprefs->getpreferences(),
            'olendsprefs'   => $lendsprefs,
            'categories'    => $categories_list,
            'statuses'      => $slist
        ];

        // display page
        $this->view->render(
            $response,
            'file:[' . $module['route'] . ']list_lent_object.tpl',
            $params
        );
        return $response;
    }
)->setName('objectslend_show_object_lend')->add($authenticate);
