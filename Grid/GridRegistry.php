<?php

namespace APY\DataGridBundle\Grid;

use APY\DataGridBundle\Grid\Column\Column;
use APY\DataGridBundle\Grid\Exception\ColumnAlreadyExistsException;
use APY\DataGridBundle\Grid\Exception\ColumnNotFoundException;
use APY\DataGridBundle\Grid\Exception\TypeAlreadyExistsException;
use APY\DataGridBundle\Grid\Exception\TypeNotFoundException;
use APY\DataGridBundle\Grid\Source\Source;
use APY\DataGridBundle\Grid\Export\Export;

/**
 * The central registry of the Grid component.
 *
 * @author  Quentin Ferrer
 */
class GridRegistry implements GridRegistryInterface
{
    /**
     * List of types.
     *
     * @var GridTypeInterface[]
     */
    private $types = [];

    /**
     * List of columns.
     *
     * @var Column[]
     */
    private $columns = [];

    /**
     * List of sources.
     *
     * @var Source[]
     */
    private $sources = [];

    /**
     * List of exports.
     *
     * @var Export[]
     */
    private $exports = [];

    /**
     * Add a grid type.
     *
     * @param GridTypeInterface $type
     *
     * @return $this
     */
    public function addType(GridTypeInterface $type)
    {
        $name = $type->getName();

        if ($this->hasType($name)) {
            throw new TypeAlreadyExistsException($name);
        }

        $this->types[$name] = $type;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getType($name)
    {
        if (!$this->hasType($name)) {
            throw new TypeNotFoundException($name);
        }

        $type = $this->types[$name];

        return $type;
    }

    /**
     * {@inheritdoc}
     */
    public function hasType($name)
    {
        if (isset($this->types[$name])) {
            return true;
        }

        return false;
    }

    /**
     * Add a column type.
     *
     * @param Column $column
     *
     * @return $this
     */
    public function addColumn(Column $column)
    {
        $type = $column->getType();

        if ($this->hasColumn($type)) {
            throw new ColumnAlreadyExistsException($type);
        }

        $this->columns[$type] = $column;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getColumn($type)
    {
        if (!$this->hasColumn($type)) {
            throw new ColumnNotFoundException($type);
        }

        $column = $this->columns[$type];

        return $column;
    }

    /**
     * {@inheritdoc}
     */
    public function hasColumn($type)
    {
        if (isset($this->columns[$type])) {
            return true;
        }

        return false;
    }

    /**
     * Add a source type.
     *
     * @param $type
     * @param Source $source
     * @return $this
     */
    public function addSource($type, Source $source)
    {
        $this->sources[$type] = $source;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getSource($type)
    {
        if (!$this->hasSource($type)) {
            throw new \Exception(sprintf("Unknown source type '%s'.", $type));
        }

        $source = $this->sources[$type];

        return $source;
    }

    /**
     * {@inheritdoc}
     */
    public function hasSource($type)
    {
        if (isset($this->sources[$type])) {
            return true;
        }

        return false;
    }

    /**
     * Add an export type.
     *
     * @param $type
     * @param Export $export
     * @return $this
     */
    public function addExport($type, Export $export)
    {
        $this->exports[$type] = $export;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getExport($type)
    {
        if (!$this->hasExport($type)) {
            throw new \Exception(sprintf("Unknown export type '%s'.", $type));
        }

        $export = $this->exports[$type];

        return $export;
    }

    /**
     * {@inheritdoc}
     */
    public function hasExport($type)
    {
        if (isset($this->exports[$type])) {
            return true;
        }

        return false;
    }
}
