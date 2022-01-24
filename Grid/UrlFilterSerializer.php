<?php
namespace APY\DataGridBundle\Grid;

/**
 * URL serializer, string format e.g.:
 * /filter1/operator1|value1/filter2/operator2|value2,value3/.../order/filter3|asc/page/2
 *
 * Class UrlFilterSerializer
 * @package APY\DataGridBundle\Grid
 */
class UrlFilterSerializer extends AbstractFilterSerializer
{
    protected $filterSeparator;
    protected $operatorSeparator;
    protected $valueSeparator;
    protected $orderIdentifier;
    protected $pageIdentifier;

    public function __construct(
        string $filterSeparator = "/",
        $operatorSeparator = "|",
        $valueSeparator = ",",
        $orderIdentifier = "order",
        $pageIdentifier = "page",
    )
    {
        $this->filterSeparator = $filterSeparator;
        $this->operatorSeparator = $operatorSeparator;
        $this->valueSeparator = $valueSeparator;
        $this->orderIdentifier = $orderIdentifier;
        $this->pageIdentifier = $pageIdentifier;
    }

    public function serialize(): string
    {
        $parts = [];

        foreach ($this->getFilters() as $name => $filter) {
            $value = $filter->getValue();
            if (is_array($value)) {
                $value = implode($this->valueSeparator, $value);
            }
            $parts[] = $name . $this->filterSeparator . $filter->getOperator() . $this->operatorSeparator . $value;
        }

        $orderColumn = $this->getOrderColumn();
        if (!empty($orderColumn)) {
            $direction = empty($this->getOrderDirection()) ? "asc" : $this->getOrderDirection();
            $parts[] = $this->orderIdentifier . $this->filterSeparator . $orderColumn . $this->operatorSeparator . $direction;
        }

        $page = $this->getPage();
        if (!empty($page) && $page != 1) {
            $parts[] = $this->pageIdentifier . $this->filterSeparator . $page;
        }

        return implode($this->filterSeparator, $parts);
    }

    public function unserialize(string $string)
    {
        $this->clear();

        $filterSeparatorPattern = "\\" . $this->filterSeparator;
        $pattern = "/([^" . $filterSeparatorPattern . "]+" . $filterSeparatorPattern . "[^" . $filterSeparatorPattern . "]+)/";
        preg_match_all($pattern, $string, $matches);

        if ($matches && isset($matches[0]) && !empty($matches[0])) {
            $pairs = $matches[0];

            $filters = $this->getFilters();

            foreach ($pairs as $pair) {
                $operator = $value = null;
                list($key, $val) = explode($this->filterSeparator, $pair);
                if (in_array($key, [$this->orderIdentifier, $this->pageIdentifier])) {
                    $value = $val;
                } else {
                    list($operator, $value) = explode($this->operatorSeparator, $val);
                }

                if (strpos($value, $this->valueSeparator) !== false) {
                    $value = explode($this->valueSeparator, $value);
                    $value["from"] = $value[0];
                    $value["to"] = $value[1];
                    unset($value[0]);
                    unset($value[1]);
                }

                switch ($key) {
                    case $this->pageIdentifier:
                        $this->setPage($value);
                        break;
                    case $this->orderIdentifier:
                        list($orderColumn, $orderDirection) = explode($this->operatorSeparator, $value);
                        $this->setOrderColumn($orderColumn);
                        $this->setOrderDirection($orderDirection);
                        break;
                    default:
                        $filter = new Filter($operator, $value);
                        $filters[$key] = $filter;
                        break;
                }
            }

            $this->setFilters($filters);
        }
    }
}
