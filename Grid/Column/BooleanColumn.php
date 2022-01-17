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

class BooleanColumn implements ColumnInterface
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
        $params['filter'] = 'select';
        $params['selectFrom'] = 'values';
        $params['operators'] = [Column::OPERATOR_EQ];
        $params['defaultOperator'] = Column::OPERATOR_EQ;
        $params['operatorsVisible'] = false;
        $params['selectMulti'] = false;

        $this->column->__initialize($params);

        $this->column->setAlign($this->column->getParam('align', 'center'));
        $this->column->setSize($this->column->getParam('size', '30'));
        $this->column->setValues($this->column->getParam('values', [1 => 'true', 0 => 'false']));

        $this->column->setIsQueryValidCallback([$this, "isQueryValid"]);
    }

    public function isQueryValid($query)
    {
        $query = (array) $query;
        if ($query[0] === true || $query[0] === false || $query[0] == 0 || $query[0] == 1) {
            return true;
        }

        return false;
    }

    public function renderCell($value, $row, $router)
    {
        $value = $this->column->renderCell($value, $row, $router);

        return $value ?: 'false';
    }

    public function getDisplayedValue($value)
    {
        return is_bool($value) ? ($value ? 1 : 0) : $value;
    }

    public function getType()
    {
        return 'boolean';
    }
}
