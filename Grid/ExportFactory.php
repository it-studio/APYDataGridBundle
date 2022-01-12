<?php
namespace APY\DataGridBundle\Grid;

use APY\DataGridBundle\Grid\Export\ExportInterface;

class ExportFactory implements ExportFactoryInterface
{
    protected $registry;

    public function __construct(GridRegistryInterface $registry)
    {
        $this->registry = $registry;
    }

    public function create(string $type, array $parameters = []): ExportInterface
    {
        if (!$this->registry->hasExport($type)) {
            throw new \Exception(sprintf("Can't find grid export type '%s'.", $type));
        }

        $defaults = [
            "title" => null,
            "fileName" => "export",
            "params" => [],
            "charset" => "UTF-8",
            "role" => null,
        ];

        $export = clone $this->registry->getExport($type);
        $export->setup(
            isset($parameters["title"]) ? $parameters["title"] : $defaults["title"],
            isset($parameters["filename"]) ? $parameters["filename"] : $defaults["filename"],
            isset($parameters["params"]) ? $parameters["params"] : $defaults["params"],
            isset($parameters["charset"]) ? $parameters["charset"] : $defaults["charset"],
            isset($parameters["role"]) ? $parameters["role"] : $defaults["role"]
        );

        return $export;
    }
}
