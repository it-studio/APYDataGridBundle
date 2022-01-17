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

class SimpleArrayColumn implements ColumnInterface
{
    use ColumnAccessTrait;

    protected $column;

    public function __construct(Column $column, $params = null)
    {
        $this->column = $column;
        $this->__initialize((array) $params);
    }

    public function __initialize(array $params)
    {
        $this->column->__initialize($params);

        $this->column->setOperators($this->column->getParam('operators', [
            Column::OPERATOR_LIKE,
            Column::OPERATOR_NLIKE,
            Column::OPERATOR_EQ,
            Column::OPERATOR_NEQ,
            Column::OPERATOR_ISNULL,
            Column::OPERATOR_ISNOTNULL,
        ]));
        $this->column->setDefaultOperator($this->column->getParam('defaultOperator', Column::OPERATOR_LIKE));
    }

    public function getFilters($source)
    {
        $parentFilters = $this->column->getFilters($source);

        $filters = [];
        foreach ($parentFilters as $filter) {
            switch ($filter->getOperator()) {
                case Column::OPERATOR_EQ:
                case Column::OPERATOR_NEQ:
                    $value = $filter->getValue();
                    $filters[] = new Filter($filter->getOperator(), $value);
                    break;
                case Column::OPERATOR_LIKE:
                case Column::OPERATOR_NLIKE:
                    $value = $filter->getValue();
                    $filters[] = new Filter($filter->getOperator(), $value);
                    break;
                case Column::OPERATOR_ISNULL:
                    $filters[] = new Filter(Column::OPERATOR_ISNULL);
                    $filters[] = new Filter(Column::OPERATOR_EQ, '');
                    $this->setDataJunction(Column::DATA_DISJUNCTION);
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

    public function renderCell($values, $row, $router)
    {
        if (is_callable($this->column->callback)) {
            return call_user_func($this->column->callback, $values, $row, $router);
        }

        // @todo: when it has an array as value?
        $return = [];
        if (is_array($values) || $values instanceof \Traversable) {
            foreach ($values as $key => $value) {
                if (!is_array($value) && isset($this->column->values[(string) $value])) {
                    $value = $this->column->values[$value];
                }

                $return[$key] = $value;
            }
        }

        return $return;
    }

    public function getType()
    {
        return 'simple_array';
    }
}
