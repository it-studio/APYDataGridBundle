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

class BlankColumn implements ColumnInterface
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
        $params['filterable'] = false;
        $params['sortable'] = false;
        $params['source'] = false;

        $this->column->__initialize($params);
    }

    public function getType()
    {
        return 'blank';
    }
}
