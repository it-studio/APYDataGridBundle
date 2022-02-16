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
    protected $rangeSeparator;
    protected $valueSeparator;
    protected $orderIdentifier;
    protected $pageIdentifier;

    public function __construct(
        string $filterSeparator = "/",
        $operatorSeparator = "|",
        $rangeSeparator = "-",
        $valueSeparator = ",",
        $orderIdentifier = "order",
        $pageIdentifier = "page",
    )
    {
        $this->filterSeparator = $filterSeparator;
        $this->operatorSeparator = $operatorSeparator;
        $this->rangeSeparator = $rangeSeparator;
        $this->valueSeparator = $valueSeparator;
        $this->orderIdentifier = $orderIdentifier;
        $this->pageIdentifier = $pageIdentifier;
    }

    public function serialize(): string
    {
        $parts = [];

        foreach ($this->getFilters() as $name => $filter) {
            $value = $filter->getValue();
            $from = $value;
            if (isset($value["from"]) && !empty($value["from"])) {
                $from = $value["from"];
            }
            $to = isset($value["to"]) && !empty($value["to"]) ? $value["to"] : null;

            $from = is_array($from) ? implode($this->valueSeparator, $from) : $from;
            $to = is_array($to) ? implode($this->valueSeparator, $to) : $to;

            $value = $from;
            if (!empty($to)) {
                $value .= $this->rangeSeparator . $to;
            }

            $parts[] = $this->escapeSpecialChars($name) . $this->filterSeparator . $filter->getOperator() . $this->operatorSeparator . $this->escapeSpecialChars($value);
        }

        $orderColumn = $this->getOrderColumn();
        if (!empty($orderColumn)) {
            $direction = empty($this->getOrderDirection()) ? "asc" : $this->getOrderDirection();
            $parts[] = $this->orderIdentifier . $this->filterSeparator . $this->escapeSpecialChars($orderColumn) . $this->operatorSeparator . $direction;
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
                list($key, $val) = explode($this->filterSeparator, $pair);

                switch ($key) {
                    case $this->pageIdentifier:
                        $this->setPage($val);
                        break;
                    case $this->orderIdentifier:
                        list($orderColumn, $orderDirection) = explode($this->operatorSeparator, $val);
                        $orderColumn = $this->unescapeSpecialChars($orderColumn);
                        $this->setOrderColumn($orderColumn);
                        $this->setOrderDirection($orderDirection);
                        break;
                    default:
                        list($operator, $value) = explode($this->operatorSeparator, $val);

                        $hlp = explode($this->rangeSeparator, $value);

                        $from = $hlp[0];
                        $from = explode($this->valueSeparator, $from);
                        if (count($from) === 1) {
                            $from = reset($from);
                            $from = $this->unescapeSpecialChars($from);
                        } else {
                            $self = $this;
                            $from = array_map(function($a) use ($self) {
                                return $self->unescapeSpecialChars($a);
                            }, $from);
                        }

                        $to = isset($hlp[1]) ? $hlp[1] : null;
                        if (null !== $to) {
                            $to = explode($this->valueSeparator, $to);
                            if (count($to) === 1) {
                                $to = reset($to);
                                $to = $this->unescapeSpecialChars($to);
                            } else {
                                $self = $this;
                                $to = array_map(function ($a) use ($self) {
                                    return $self->unescapeSpecialChars($a);
                                }, $to);
                            }
                        }

                        $value = [
                            "from" => $from,
                        ];

                        if (!empty($to)) {
                            $value["to"] = $to;
                        }

                        $filter = new Filter($operator, $value);
                        $filters[$key] = $filter;
                        break;
                }
            }

            $this->setFilters($filters);
        }
    }

    protected function getSpecialCharsTable()
    {
        return [
            $this->filterSeparator => "%f",
            $this->operatorSeparator => "%o",
            $this->rangeSeparator => "%r",
            $this->valueSeparator => "%v",
        ];
    }

    public function escapeSpecialChars(string $value)
    {
        $chars = $this->getSpecialCharsTable();

        foreach ($chars as $original => $replacement) {
            $value = str_replace($original, $replacement, $value);
        }

        return $value;
    }

    public function unescapeSpecialChars(string $value)
    {
        $chars = $this->getSpecialCharsTable();

        foreach ($chars as $original => $replacement) {
            $value = str_replace($replacement, $original, $value);
        }

        return $value;
    }
}
