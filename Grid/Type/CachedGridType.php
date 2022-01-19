<?php

namespace APY\DataGridBundle\Grid\Type;

use APY\DataGridBundle\Grid\AbstractType;
use APY\DataGridBundle\Grid\GridBuilder;
use APY\DataGridBundle\Grid\GridBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

/**
 * grid type which supports column caching
 *
 * @author Honza Vavra
 */
class CachedGridType extends GridType
{
    protected $cache;
    protected $cacheGridBuilderClass;

    public function __construct(CacheInterface $cache, string $cacheGridBuilderClass)
    {
        $this->cache = $cache;
        $this->cacheGridBuilderClass = $cacheGridBuilderClass;
    }

    /**
     * {@inheritdoc}
     */
    public function buildGrid(GridBuilder $builder, array $options = [])
    {
        parent::buildGrid($builder, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'cached_grid';
    }

    protected function getColumnsCacheKey(string $gridIdentifier = null):string
    {
        $key = $this->getName();

        if (!empty($gridIdentifier)) {
            $key .= "_" . $gridIdentifier;
        }

        return md5($key);
    }

    /**
     * column definition can be set to fake GridBuilder in a callback, it is processed only
     * for the first time, then it is loaded from cache
     *
     * @param callable $callback it is called with one CachedGridBuilderInterface parameter
     * @param GridBuilderInterface $builder original GridBuilder
     * @param string|null $gridIdentifier grid instance identifier (so more instances of this type can exist simultaneously)
     */
    public function addCachedColumns(callable $callback, GridBuilderInterface $builder, string $gridIdentifier = null)
    {
        $key = $this->getColumnsCacheKey($gridIdentifier);

        $cachedGridBuilder = new $this->cacheGridBuilderClass;
        $serializedCachedGridBuilder = $this->cache->get($key, function (ItemInterface $item) use ($cachedGridBuilder, $callback) {
            call_user_func($callback, $cachedGridBuilder);

            return $cachedGridBuilder->serialize();
        });

        $cachedGridBuilder->unserialize($serializedCachedGridBuilder);

        // populating original GridBuilder from cached columns information
        foreach ($cachedGridBuilder->getColumns() as $columnData) {
            $builder->add($columnData["name"], $columnData["type"], $columnData["options"]);
        }
    }

    /**
     * this can be called to explicitly invalidate columns cache
     *
     * @param string $gridIdentifier
     */
    public function invalidateColumnsCache(string $gridIdentifier)
    {
        $key = $this->getColumnsCacheKey($gridIdentifier);

        $this->cache->delete($key);

        return $this;
    }
}
