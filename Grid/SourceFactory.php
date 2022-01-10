<?php
namespace APY\DataGridBundle\Grid;

use APY\DataGridBundle\Grid\Source\Source;

class SourceFactory implements SourceFactoryInterface
{
    /**
     * registered source types
     *
     * @var array
     */
    protected $types;

    public function create(string $type, array $parameters = []): Source
    {
        if (!isset($this->types[$type])) {
            throw new \Exception(sprintf("Can't find grid source type '%s'.", $type));
        }

        $source = clone $this->types[$type];
        $source->setup($parameters);

        return $source;
    }

    public function addType(string $name, Source $type)
    {
        $this->types[$name] = $type;

        return $this;
    }
}
