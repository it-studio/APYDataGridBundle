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

class ArrayColumn implements ColumnInterface
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
    }

    public function getFilters($source)
    {
        $parentFilters = $this->column->getFilters($source);

        $filters = [];
        foreach ($parentFilters as $filter) {
            if ($source === 'document') {
                $filters[] = $filter;
            } else {
                switch ($filter->getOperator()) {
                    case Column::OPERATOR_EQ:
                    case Column::OPERATOR_NEQ:
                        $filterValues = (array) $filter->getValue();
                        $value = '';
                        $counter = 1;
                        foreach ($filterValues as $filterValue) {
                            $len = strlen($filterValue);
                            $value .= 'i:' . $counter++ . ';s:' . $len . ':"' . $filterValue . '";';
                        }

                        $filters[] = new Filter($filter->getOperator(), 'a:' . count($filterValues) . ':{' . $value . '}');
                        break;
                    case Column::OPERATOR_LIKE:
                    case Column::OPERATOR_NLIKE:
                        $len = strlen($filter->getValue());
                        $value = 's:' . $len . ':"' . $filter->getValue() . '";';
                        $filters[] = new Filter($filter->getOperator(), $value);
                        break;
                    case Column::OPERATOR_ISNULL:
                        $filters[] = new Filter(Column::OPERATOR_ISNULL);
                        $filters[] = new Filter(Column::OPERATOR_EQ, 'a:0:{}');
                        $this->column->setDataJunction(Column::DATA_DISJUNCTION);
                        break;
                    case Column::OPERATOR_ISNOTNULL:
                        $filters[] = new Filter(Column::OPERATOR_ISNOTNULL);
                        $filters[] = new Filter(Column::OPERATOR_NEQ, 'a:0:{}');
                        break;
                    default:
                        $filters[] = $filter;
                }
            }
        }

        return $filters;
    }

    public function renderCell($values, $row, $router)
    {
        if (is_callable($this->column->callback)) {
            return call_user_func($this->column->callback, $values, $row, $router);
        }

        $return = [];
        if (is_array($values) || $values instanceof \Traversable) {
            foreach ($values as $key => $value) {
                // @todo: this seems like dead code
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
        return 'array';
    }
}
