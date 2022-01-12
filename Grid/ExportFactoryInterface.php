<?php
namespace APY\DataGridBundle\Grid;

use APY\DataGridBundle\Grid\Export\ExportInterface;

interface ExportFactoryInterface
{
    /**
     * creates instanceof export type with specific parameters
     *
     * @param string $type
     * @param array $parameters
     *
     * @return ExportInterface
     */
    public function create(string $type, array $parameters = []): ExportInterface;
}
