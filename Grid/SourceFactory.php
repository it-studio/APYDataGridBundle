<?php
namespace APY\DataGridBundle\Grid;

use APY\DataGridBundle\Grid\Source\Source;

class SourceFactory implements SourceFactoryInterface
{
    protected $registry;

    public function __construct(GridRegistryInterface $registry)
    {
        $this->registry = $registry;
    }

    public function create(string $type, array $parameters = []): Source
    {
        if (!$this->registry->hasSource($type)) {
            throw new \Exception(sprintf("Can't find grid source type '%s'.", $type));
        }

        $source = clone $this->registry->getSource($type);
        $source->setup($parameters);

        return $source;
    }
}
