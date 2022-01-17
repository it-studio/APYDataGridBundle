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

class JoinColumn extends TextColumn
{
    use ColumnAccessTrait;

    protected $column;

    public function __construct(Column $column, $params = null)
    {
        $this->column = $column;
        $this->__initialize((array) $params);
    }

    protected $joinColumns = [];

    protected $dataJunction = Column::DATA_DISJUNCTION;

    public function __initialize(array $params)
    {
        $this->column->__initialize($params);

        $this->setJoinColumns($this->column->getParam('columns', []));
        $this->column->setSeparator($this->column->getParam('separator', '&nbsp;'));

        $this->column->setVisibleForSource(true);
        $this->column->setIsManualField(true);
    }

    public function setJoinColumns(array $columns)
    {
        $this->joinColumns = $columns;
    }

    public function getJoinColumns()
    {
        return $this->joinColumns;
    }

    public function getFilters($source)
    {
        $filters = [];

        // Apply same filters on each column
        foreach ($this->joinColumns as $columnName) {
            $tempFilters = $this->column->getFilters($source);

            foreach ($tempFilters as $filter) {
                $filter->setColumnName($columnName);
            }

            $filters = array_merge($filters, $tempFilters);
        }

        return $filters;
    }

    public function getType()
    {
        return 'join';
    }
}
