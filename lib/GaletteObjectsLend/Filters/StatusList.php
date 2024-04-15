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

namespace GaletteObjectsLend\Filters;

use Analog\Analog;
use Galette\Core\Pagination;
use GaletteObjectsLend\Repository\Status;

/**
 * Status list filters and paginator
 *
 * @author Johan Cwiklinski <johan@x-tnd.be>
 *
 * @property ?string $filter_str
 * @property ?int $active_filter
 * @property ?int $stock_filter
 * @property string $query
 */

class StatusList extends Pagination
{
    //filters
    private ?string $filter_str;
    private ?int $active_filter;
    private ?int $stock_filter;

    protected string $query;

    /** @var array<string> */
    protected array $statuslist_fields = array(
        'filter_str',
        'active_filter',
        'stock_filter',
        'query'
    );

    /**
     * Returns the field we want to default set order to
     *
     * @return string field name
     */
    protected function getDefaultOrder(): string
    {
        return 'status_text';
    }

    /**
     * Reinit default parameters
     *
     * @return void
     */
    public function reinit(): void
    {
        parent::reinit();
        $this->filter_str = null;
        $this->active_filter = null;
        $this->stock_filter = null;
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
        if (in_array($name, $this->pagination_fields)) {
            return parent::__get($name);
        } else {
            if (in_array($name, $this->statuslist_fields)) {
                return $this->$name;
            }
        }

        throw new \RuntimeException(
            sprintf(
                'Unable to get property "%s::%s"!',
                __CLASS__,
                $name
            )
        );
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

        if (in_array($name, $this->pagination_fields)) {
            parent::__set($name, $value);
        } else {
            Analog::log(
                '[StatusList] Setting property `' . $name . '`',
                Analog::DEBUG
            );

            switch ($name) {
                case 'filter_str':
                    $this->$name = $value;
                    break;
                case 'active_filter':
                    switch ($value) {
                        case Status::ALL:
                        case Status::ACTIVE:
                        case Status::INACTIVE:
                            $this->active_filter = $value;
                            break;
                        default:
                            Analog::log(
                                '[StatusList] Value for active filter should be either ' .
                                Status::ACTIVE . ' or ' .
                                Status::INACTIVE . ' (' . $value . ' given)',
                                Analog::WARNING
                            );
                            break;
                    }
                    break;
                case 'stock_filter':
                    switch ($value) {
                        case Status::DC_STOCK:
                        case Status::IN_STOCK:
                        case Status::OUT_STOCK:
                            $this->stock_filter = $value;
                            break;
                        default:
                            Analog::log(
                                '[StatusList] Value for stock filter should be either ' .
                                Status::IN_STOCK . ' or ' .
                                Status::OUT_STOCK . ' (' . $value . ' given)',
                                Analog::WARNING
                            );
                            break;
                    }

                    break;
                case 'query':
                    $this->$name = $value;
                    break;
                default:
                    Analog::log(
                        '[StatusList] Unable to set proprety `' . $name . '`',
                        Analog::WARNING
                    );
                    break;
            }
        }
    }
}
