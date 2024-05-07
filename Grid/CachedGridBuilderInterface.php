<?php
namespace APY\DataGridBundle\Grid;

/**
 * fake builder - stores column definition data, which are then used for caching
 */
interface CachedGridBuilderInterface extends \Serializable
{
    public function add(string $name, string $type, array $options = []);
    public function remove(string $name);
    public function has(string $name): boolean;
    public function get(string $name): array|null;
    public function getColumns(): array;
}
