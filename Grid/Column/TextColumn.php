<?php

/*
 * This file is part of the DataGridBundle.
 *
 * (c) Abhoryo <abhoryo@free.fr>
 * (c) Stanislav Turza
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace APY\DataGridBundle\Grid\Column;

use APY\DataGridBundle\Grid\Filter;

class TextColumn implements ColumnInterface
{
    use ColumnAccessTrait;

    protected $column;

    public function __construct(Column $column, $params = null)
    {
        $this->column = $column;
        $this->__initialize((array) $params);

        $this->column->setIsQueryValidCallback([$this, "isQueryValid"]);
    }

    // changes

    public function isQueryValid($query)
    {
        $result = array_filter((array) $query, 'is_string');

        return !empty($result);
    }

    public function getFilters($source)
    {
        $parentFilters = $this->column->getFilters($source);

        $filters = [];
        foreach ($parentFilters as $filter) {
            switch ($filter->getOperator()) {
                case Column::OPERATOR_ISNULL:
                    $filters[] = new Filter(Column::OPERATOR_ISNULL);
                    $filters[] = new Filter(Column::OPERATOR_EQ, '');
                    $this->column->setDataJunction(Column::DATA_DISJUNCTION);
                    break;
                case Column::OPERATOR_ISNOTNULL:
                    $filters[] = new Filter(Column::OPERATOR_ISNOTNULL);
                    $filters[] = new Filter(Column::OPERATOR_NEQ, '');
                    break;
                default:
                    $filters[] = $filter;
            }
        }

        return $filters;
    }

    public function getType()
    {
        return 'text';
    }
}
