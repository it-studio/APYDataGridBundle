<?php
namespace APY\DataGridBundle\Grid;

abstract class AbstractFilterSerializer implements \Serializable
{
    const ORDER_SEPARATOR = "|";

    protected $filters = [];

    protected $page;

    protected $orderColumn;
    /**
     * @var string asc or desc
     */
    protected $orderDirection;

    public function getFilters(): array
    {
        return $this->filters;
    }

    public function setFilters(array $filters = [])
    {
        $this->filters = $filters;

        return $this;
    }

    public function getPage(): int|null
    {
        return $this->page;
    }

    public function setPage(int $page = null)
    {
        $this->page = $page;

        return $this;
    }

    public function getOrderColumn(): string|null
    {
        return $this->orderColumn;
    }

    public function setOrderColumn(string $columnId = null)
    {
        $this->orderColumn = $columnId;

        return $this;
    }

    public function getOrderDirection(): string|null
    {
        return $this->orderDirection;
    }

    public function setOrderDirection(string $orderDirection = null)
    {
        $this->orderDirection = $orderDirection;

        return $this;
    }

    public function setFromGrid(GridInterface $grid)
    {
        $hash = $grid->getHash();

        if (empty($hash)) {
            throw new \Exception("Grid filters are only available after the call of the method isRedirected of the grid.");
        }

        $this->setFilters($grid->getFilters());

        $this->setPage($grid->getPage() + 1);

        $order = $grid->getOrder();
        if (!empty($order)) {
            list($columnId, $direction) = explode(self::ORDER_SEPARATOR, $order);
            $this->setOrderColumn($columnId);
            $this->setOrderDirection($direction);
        }

        return $this;
    }

    public function setToGrid(GridInterface $grid)
    {
        $sessionData = $grid->getSessionDataFromFilters($this->getFilters());

        $grid->setDefaultFilters($sessionData);

        if (!empty($this->getOrderColumn())) {
            $grid->setDefaultOrder($this->getOrderColumn(), $this->getOrderDirection());
        }

        if (!empty($this->getPage())) {
            $grid->setDefaultPage($this->getPage());
        }

        return $this;
    }

    abstract public function serialize(): string;
    abstract public function unserialize(string $string);

    public function clear()
    {
        $this->filters = [];
        $this->page = null;
        $this->orderColumn = null;
        $this->orderDirection = null;
    }
}
