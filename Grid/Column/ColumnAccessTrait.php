<?php
namespace APY\DataGridBundle\Grid\Column;

/**
 * every column type should use this so it can delegate calling methods to Column object inside
 *
 * Trait ColumnAccessTrait
 * @package APY\DataGridBundle\Grid\Column
 */
trait ColumnAccessTrait
{
    /**
     * every unknown method call is delegated to Column service injected inside
     *
     * @param string $name
     * @param array $arguments
     * @return mixed
     * @throws \Exception
     */
    public function __call(string $name, array $arguments): mixed
    {
        $methodName = $this->guessColumnMethodName($name);

        if (!$methodName) {
            throw new \Exception(sprintf("Unknown column method name '%s'.", $name));
        }

        return call_user_func_array([$this->column, $methodName], $arguments);
    }

    /**
     * unknown method name resolver
     *
     * @param $name
     * @return string|null
     */
    protected function guessColumnMethodName($name)
    {
        if (method_exists($this->column, $name)) {
            return $name;
        }

        $prefixes = ["is", "get"];

        foreach ($prefixes as $prefix) {
            $altName = $prefix . ucfirst($name);
            if (method_exists($this->column, $altName)) {
                return $altName;
            }
        }

        return null;
    }

    /**
     * when cloning the column type, injected Column service has to be also cloned
     */
    public function __clone()
    {
        $this->column = clone $this->column;
        $this->column->setIsQueryValidCallback(null);
    }

    /**
     * this can't be called by magic, because it's parameter is given as reference
     */
    public function hasDQLFunction(&$matches = null)
    {
        return $this->column->hasDQLFunction($matches);
    }
}
