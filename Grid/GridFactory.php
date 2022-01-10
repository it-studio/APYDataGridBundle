<?php

namespace APY\DataGridBundle\Grid;

use APY\DataGridBundle\Grid\Column\Column;
use APY\DataGridBundle\Grid\Exception\UnexpectedTypeException;
use APY\DataGridBundle\Grid\Source\Source;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * Class GridFactory.
 *
 * @author  Quentin Ferrer
 */
class GridFactory implements GridFactoryInterface
{
    /**
     * @var GridRegistryInterface
     */
    private $registry;

    protected $requestStack;
    protected $router;
    protected $authorizationChecker;
    protected $httpKernel;
    protected $twig;

    /**
     * Constructor.
     *
     * @param GridRegistryInterface $registry  The grid registry
     */
    public function __construct(
        GridRegistryInterface $registry,
        object $requestStack,
        RouterInterface $router,
        AuthorizationCheckerInterface $authorizationChecker,
        HttpKernelInterface $httpKernel,
        object $twig,
    )
    {
        $this->registry = $registry;
        $this->requestStack = $requestStack;
        $this->router = $router;
        $this->authorizationChecker = $authorizationChecker;
        $this->httpKernel = $httpKernel;
        $this->twig = $twig;
    }

    /**
     * {@inheritdoc}
     */
    public function create($type = null, Source $source = null, array $options = [])
    {
        return $this->createBuilder($type, $source, $options)->getGrid();
    }

    /**
     * {@inheritdoc}
     */
    public function createBuilder($type = 'grid', Source $source = null, array $options = [])
    {
        $type = $this->resolveType($type);
        $options = $this->resolveOptions($type, $source, $options);

        $builder = new GridBuilder(
            $this,
            $this->requestStack,
            $this->router,
            $this->authorizationChecker,
            $this->httpKernel,
            $this->twig,
            $type->getName(),
            $options
        );
        $builder->setType($type);

        $type->buildGrid($builder, $options);

        return $builder;
    }

    /**
     * {@inheritdoc}
     */
    public function createColumn($name, $type, array $options = [])
    {
        if (!$type instanceof Column) {
            if (!is_string($type)) {
                throw new UnexpectedTypeException($type, 'string, APY\DataGridBundle\Grid\Column\Column');
            }

            $column = clone $this->registry->getColumn($type);

            $column->__initialize(array_merge([
                'id'     => $name,
                'title'  => $name,
                'field'  => $name,
                'source' => true,
            ], $options));
        } else {
            $column = $type;
            $column->setId($name);
        }

        return $column;
    }

    /**
     * Returns an instance of type.
     *
     * @param string|GridTypeInterface $type The type of the grid
     *
     * @return GridTypeInterface
     */
    private function resolveType($type)
    {
        if (!$type instanceof GridTypeInterface) {
            if (!is_string($type)) {
                throw new UnexpectedTypeException($type, 'string, APY\DataGridBundle\Grid\GridTypeInterface');
            }

            $type = $this->registry->getType($type);
        }

        return $type;
    }

    /**
     * Returns the options resolved.
     *
     * @param GridTypeInterface $type
     * @param Source            $source
     * @param array             $options
     *
     * @return array
     */
    private function resolveOptions(GridTypeInterface $type, Source $source = null, array $options = [])
    {
        $resolver = new OptionsResolver();

        $type->configureOptions($resolver);

        if (null !== $source && !isset($options['source'])) {
            $options['source'] = $source;
        }

        $options = $resolver->resolve($options);

        return $options;
    }
}
