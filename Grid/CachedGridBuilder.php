<?php
namespace APY\DataGridBundle\Grid;

class CachedGridBuilder implements CachedGridBuilderInterface
{
    protected $columns = [];

    public function add(string $name, string $type, array $options = [])
    {
        $this->columns[$name] = [
            "name" => $name,
            "type" => $type,
            "options" => $options,
        ];

        return $this;
    }

    public function remove(string $name)
    {
        if ($this->has($name)) {
            unset($this->columns[$name]);
        }

        return $this;
    }

    public function has(string $name): boolean
    {
        return isset($this->columns[$name]);
    }

    public function get(string $name): array|null
    {
        if ($this->has($name)) {
            return $this->columns[$name];
        }

        return null;
    }

    public function serialize(): string
    {
        return serialize($this->columns);
    }

    public function unserialize(string $data)
    {
        $this->columns = unserialize($data);
    }

    public function getColumns(): array
    {
        return $this->columns;
    }
}
