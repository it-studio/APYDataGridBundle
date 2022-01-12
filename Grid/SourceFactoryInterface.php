<?php
namespace APY\DataGridBundle\Grid;

use APY\DataGridBundle\Grid\Source\Source;

interface SourceFactoryInterface
{
    /**
     * creates instanceof source type with specific parameters
     *
     * @param string $type
     * @param array $parameters
     *
     * @return Source
     */
    public function create(string $type, array $parameters = []): Source;
}
