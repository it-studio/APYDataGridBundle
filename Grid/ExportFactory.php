<?php
namespace APY\DataGridBundle\Grid;

use APY\DataGridBundle\Grid\Export\ExportInterface;

class ExportFactory implements ExportFactoryInterface
{
    /**
     * registered export types
     *
     * @var array
     */
    protected $types;

    public function create(string $type, array $parameters = []): ExportInterface
    {
        if (!isset($this->types[$type])) {
            throw new \Exception(sprintf("Can't find grid export type '%s'.", $type));
        }

        $defaults = [
            "title" => null,
            "fileName" => "export",
            "params" => [],
            "charset" => "UTF-8",
            "role" => null,
        ];

        $export = clone $this->types[$type];
        $export->setup(
            isset($parameters["title"]) ? $parameters["title"] : $defaults["title"],
            isset($parameters["filename"]) ? $parameters["filename"] : $defaults["filename"],
            isset($parameters["params"]) ? $parameters["params"] : $defaults["params"],
            isset($parameters["charset"]) ? $parameters["charset"] : $defaults["charset"],
            isset($parameters["role"]) ? $parameters["role"] : $defaults["role"]
        );

        return $export;
    }

    public function addType(string $name, ExportInterface $type)
    {
        $this->types[$name] = $type;

        return $this;
    }
}
