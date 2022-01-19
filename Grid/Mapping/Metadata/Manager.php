<?php

/*
 * This file is part of the DataGridBundle.
 *
 * (c) Abhoryo <abhoryo@free.fr>
 * (c) Stanislav Turza
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @todo check for column extensions
 */

namespace APY\DataGridBundle\Grid\Mapping\Metadata;

use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class Manager
{
    /**
     * @var \APY\DataGridBundle\Grid\Mapping\Driver\DriverInterface[]
     */
    protected $drivers;

    protected $cache;
    protected $cacheExpiration;

    public function __construct(CacheInterface $cache, int $cacheExpiration = null)
    {
        $this->cache = $cache;
        $this->cacheExpiration = $cacheExpiration;

        $this->drivers = new DriverHeap();
    }

    public function addDriver($driver, $priority)
    {
        $this->drivers->insert($driver, $priority);
    }

    /**
     * @todo remove this hack
     *
     * @return \APY\DataGridBundle\Grid\Mapping\Metadata\DriverHeap
     */
    public function getDrivers()
    {
        return clone $this->drivers;
    }

    public function getMetadata($className, $group = 'default')
    {
        $metadata = new Metadata();

        $self = $this;

        $key = $this->getCacheKey($className, $group);
        $cacheExpiration = $this->cacheExpiration;
        $data = $this->cache->get($key, function (ItemInterface $item) use ($self, $metadata, $className, $group, $cacheExpiration) {
            if (!empty($cacheExpiration)) {
                $item->expiresAfter($cacheExpiration);
            }

            $columns = $fieldsMetadata = $groupBy = [];

            foreach ($self->getDrivers() as $driver) {
                $columns = array_merge($columns, $driver->getClassColumns($className, $group));
                $fieldsMetadata[] = $driver->getFieldsMetadata($className, $group);
                $groupBy = array_merge($groupBy, $driver->getGroupBy($className, $group));
            }

            $mappings = $cols = [];

            foreach ($columns as $fieldName) {
                $map = [];

                foreach ($fieldsMetadata as $field) {
                    if (isset($field[$fieldName]) && (!isset($field[$fieldName]['groups']) || in_array($group, (array) $field[$fieldName]['groups']))) {
                        $map = array_merge($map, $field[$fieldName]);
                    }
                }

                if (!empty($map)) {
                    $mappings[$fieldName] = $map;
                    $cols[] = $fieldName;
                }
            }

            $metadata->setFields($cols);
            $metadata->setFieldsMappings($mappings);
            $metadata->setGroupBy($groupBy);

            return $metadata->serialize();
        });

        $metadata->unserialize($data);

        return $metadata;
    }

    protected function getCacheKey($className, $group)
    {
        $key = $className;

        if (!empty($group)) {
            $key .= "_" . $group;
        }

        return md5($key);
    }
}
