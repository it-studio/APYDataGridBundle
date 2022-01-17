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

class MassActionColumn implements ColumnInterface
{
    use ColumnAccessTrait;

    protected $column;

    const ID = '__action';

    public function __construct(Column $column)
    {
        $this->column = $column;

        $this->column->__construct([
            'id'         => self::ID,
            'title'      => '',
            'size'       => 15,
            'filterable' => true,
            'sortable'   => false,
            'source'     => false,
            'align'      => Column::ALIGN_CENTER,
        ]);
    }

    public function isVisible($isExported = false)
    {
        if ($isExported) {
            return false;
        }

        return $this->column->isVisible();
    }

    public function getFilterType()
    {
        return $this->getType();
    }

    public function getType()
    {
        return 'massaction';
    }
}
