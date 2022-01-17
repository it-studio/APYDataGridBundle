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

class DateColumn extends DateTimeColumn
{
    protected $timeFormat = \IntlDateFormatter::NONE;

    protected $fallbackFormat = 'Y-m-d';

    protected $fallbackInputFormat = 'Y-m-d';

    public function getFilters($source)
    {
        $parentFilters = parent::getFilters($source);

        $filters = [];
        foreach ($parentFilters as $filter) {
            if ($filter->getValue() !== null) {
                $dateFrom = $filter->getValue();
                $dateFrom->setTime(0, 0, 0);

                $dateTo = clone $dateFrom;
                $dateTo->setTime(23, 59, 59);

                switch ($filter->getOperator()) {
                    case Column::OPERATOR_EQ:
                        $filters[] = new Filter(Column::OPERATOR_GTE, $dateFrom);
                        $filters[] = new Filter(Column::OPERATOR_LTE, $dateTo);
                        break;
                    case Column::OPERATOR_NEQ:
                        $filters[] = new Filter(Column::OPERATOR_LT, $dateFrom);
                        $filters[] = new Filter(Column::OPERATOR_GT, $dateTo);
                        $this->setDataJunction(Column::DATA_DISJUNCTION);
                        break;
                    case Column::OPERATOR_LT:
                    case Column::OPERATOR_GTE:
                        $filters[] = new Filter($filter->getOperator(), $dateFrom);
                        break;
                    case Column::OPERATOR_GT:
                    case Column::OPERATOR_LTE:
                        $filters[] = new Filter($filter->getOperator(), $dateTo);
                        break;
                    default:
                        $filters[] = $filter;
                }
            } else {
                $filters[] = $filter;
            }
        }

        return $filters;
    }

    public function getType()
    {
        return 'date';
    }
}
