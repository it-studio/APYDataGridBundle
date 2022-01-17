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

class RankColumn extends BlankColumn
{
    protected $rank = 1;

    public function __initialize(array $params)
    {
        parent::__initialize($params);

        $this->column->setId($this->column->getParam('id', 'rank'));
        $this->column->setTitle($this->column->getParam('title', 'rank'));
        $this->column->setSize($this->column->getParam('size', '30'));
        $this->column->setAlign($this->column->getParam('align', 'center'));
    }

    public function renderCell($value, $row, $router)
    {
        return $this->column->rank++;
    }

    public function getType()
    {
        return 'rank';
    }
}
