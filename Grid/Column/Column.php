<?php

/*
 * This file is part of the DataGridBundle.
 *
 * (c) Abhoryo <abhoryo@free.fr>
 * (c) Stanislav Turza
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace APY\DataGridBundle\Grid\Column;

use APY\DataGridBundle\Grid\Filter;
use APY\DataGridBundle\Grid\Row;
use Doctrine\Common\Version as DoctrineVersion;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class Column
{
    const DEFAULT_VALUE = null;

    /**
     * Filter.
     */
    const DATA_CONJUNCTION = 0;
    const DATA_DISJUNCTION = 1;

    const OPERATOR_EQ = 'eq';
    const OPERATOR_NEQ = 'neq';
    const OPERATOR_LT = 'lt';
    const OPERATOR_LTE = 'lte';
    const OPERATOR_GT = 'gt';
    const OPERATOR_GTE = 'gte';
    const OPERATOR_BTW = 'btw';
    const OPERATOR_BTWE = 'btwe';
    const OPERATOR_LIKE = 'like';
    const OPERATOR_NLIKE = 'nlike';
    const OPERATOR_RLIKE = 'rlike';
    const OPERATOR_LLIKE = 'llike';
    const OPERATOR_SLIKE = 'slike'; //simple/strict LIKE
    const OPERATOR_NSLIKE = 'nslike';
    const OPERATOR_RSLIKE = 'rslike';
    const OPERATOR_LSLIKE = 'lslike';

    const OPERATOR_ISNULL = 'isNull';
    const OPERATOR_ISNOTNULL = 'isNotNull';

    protected static $availableOperators = [
        Column::OPERATOR_EQ,
        Column::OPERATOR_NEQ,
        Column::OPERATOR_LT,
        Column::OPERATOR_LTE,
        Column::OPERATOR_GT,
        Column::OPERATOR_GTE,
        Column::OPERATOR_BTW,
        Column::OPERATOR_BTWE,
        Column::OPERATOR_LIKE,
        Column::OPERATOR_NLIKE,
        Column::OPERATOR_RLIKE,
        Column::OPERATOR_LLIKE,
        Column::OPERATOR_SLIKE,
        Column::OPERATOR_NSLIKE,
        Column::OPERATOR_RSLIKE,
        Column::OPERATOR_LSLIKE,
        Column::OPERATOR_ISNULL,
        Column::OPERATOR_ISNOTNULL,
    ];

    /**
     * Align.
     */
    const ALIGN_LEFT = 'left';
    const ALIGN_RIGHT = 'right';
    const ALIGN_CENTER = 'center';

    protected static $aligns = [
        Column::ALIGN_LEFT,
        Column::ALIGN_RIGHT,
        Column::ALIGN_CENTER,
    ];

    /**
     * Internal parameters.
     */
    public $id;
    public $title;
    public $sortable;
    public $filterable;
    public $visible;
    public $callback;
    public $order;
    public $size;
    public $visibleForSource;
    public $primary;
    public $align;
    public $inputType;
    public $field;
    public $role;
    public $filterType;
    public $params;
    public $isSorted = false;
    public $orderUrl;
    public $authorizationChecker;
    public $data;
    public $operatorsVisible;
    public $operators;
    public $defaultOperator;
    public $values = [];
    public $selectFrom;
    public $selectMulti;
    public $selectExpanded;
    public $searchOnClick = false;
    public $safe;
    public $separator;
    public $joinType;
    public $export;
    public $class;
    public $isManualField;
    public $isAggregate;
    public $usePrefixTitle;
    public $translationDomain;

    public $dataJunction = Column::DATA_CONJUNCTION;

    /**
     * this function is often called inside this service and should be replacable from outside (from column types)
     *
     * @var callback
     */
    public $isQueryValidCallback;

    /**
     * Default Column constructor.
     *
     * @param array $params
     */
    public function __construct($params = null)
    {
        $this->__initialize((array) $params);
    }

    public function __initialize(array $params)
    {
        $this->params = $params;
        $this->setId($this->getParam('id'));
        $this->setTitle($this->getParam('title', $this->getParam('field')));
        $this->setSortable($this->getParam('sortable', true));
        $this->setVisible($this->getParam('visible', true));
        $this->setSize($this->getParam('size', -1));
        $this->setFilterable($this->getParam('filterable', true));
        $this->setVisibleForSource($this->getParam('source', false));
        $this->setPrimary($this->getParam('primary', false));
        $this->setAlign($this->getParam('align', Column::ALIGN_LEFT));
        $this->setInputType($this->getParam('inputType', 'text'));
        $this->setField($this->getParam('field'));
        $this->setRole($this->getParam('role'));
        $this->setOrder($this->getParam('order'));
        $this->setJoinType($this->getParam('joinType'));
        $this->setFilterType($this->getParam('filter', 'input'));
        $this->setSelectFrom($this->getParam('selectFrom', 'query'));
        $this->setValues($this->getParam('values', []));
        $this->setOperatorsVisible($this->getParam('operatorsVisible', true));
        $this->setIsManualField($this->getParam('isManualField', false));
        $this->setIsAggregate($this->getParam('isAggregate', false));
        $this->setUsePrefixTitle($this->getParam('usePrefixTitle', true));

        // Order is important for the order display
        $this->setOperators($this->getParam('operators', [
            Column::OPERATOR_EQ,
            Column::OPERATOR_NEQ,
            Column::OPERATOR_LT,
            Column::OPERATOR_LTE,
            Column::OPERATOR_GT,
            Column::OPERATOR_GTE,
            Column::OPERATOR_BTW,
            Column::OPERATOR_BTWE,
            Column::OPERATOR_LIKE,
            Column::OPERATOR_NLIKE,
            Column::OPERATOR_RLIKE,
            Column::OPERATOR_LLIKE,
            Column::OPERATOR_SLIKE,
            Column::OPERATOR_NSLIKE,
            Column::OPERATOR_RSLIKE,
            Column::OPERATOR_LSLIKE,
            Column::OPERATOR_ISNULL,
            Column::OPERATOR_ISNOTNULL,
        ]));
        $this->setDefaultOperator($this->getParam('defaultOperator', Column::OPERATOR_LIKE));
        $this->setSelectMulti($this->getParam('selectMulti', false));
        $this->setSelectExpanded($this->getParam('selectExpanded', false));
        $this->setSearchOnClick($this->getParam('searchOnClick', false));
        $this->setSafe($this->getParam('safe', 'html'));
        $this->setSeparator($this->getParam('separator', '<br />'));
        $this->setExport($this->getParam('export'));
        $this->setClass($this->getParam('class'));
        $this->setTranslationDomain($this->getParam('translation_domain'));
    }

    public function getIsQueryValidCallback(): callable|null
    {
        return $this->isQueryValidCallback;
    }

    public function setIsQueryValidCallback(callable $isQueryValidCallback = null): Column
    {
        $this->isQueryValidCallback = $isQueryValidCallback;

        return $this;
    }

    public function getParam($id, $default = null)
    {
        return isset($this->params[$id]) ? $this->params[$id] : $default;
    }

    /**
     * Draw cell.
     *
     * @param string $value
     * @param Row    $row
     * @param $router
     *
     * @return string
     */
    public function renderCell($value, $row, $router)
    {
        if (is_callable($this->callback)) {
            return call_user_func($this->callback, $value, $row, $router);
        }

        $value = is_bool($value) ? (int) $value : $value;
        if (array_key_exists((string) $value, $this->values)) {
            $value = $this->values[$value];
        }

        return $value;
    }

    /**
     * Set column callback.
     *
     * @param  $callback
     *
     * @return self
     */
    public function manipulateRenderCell($callback)
    {
        $this->callback = $callback;

        return $this;
    }

    /**
     * Set column identifier.
     *
     * @param $id
     *
     * @return self
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * get column identifier.
     *
     * @return int|string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * get column render block identifier.
     *
     * @return int|string
     */
    public function getRenderBlockId()
    {
        // For Mapping fields and aggregate dql functions
        return str_replace(['.', ':'], '_', $this->id);
    }

    /**
     * Set column title.
     *
     * @param string $title
     *
     * @return \APY\DataGridBundle\Grid\Column\Column
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get column title.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set column visibility.
     *
     * @param bool $visible
     *
     * @return $this
     */
    public function setVisible($visible)
    {
        $this->visible = $visible;

        return $this;
    }

    /**
     * Return column visibility.
     *
     * @return bool return true when column is visible
     */
    public function isVisible($isExported = false)
    {
        $visible = $isExported && $this->export !== null ? $this->export : $this->visible;

        if ($visible && $this->authorizationChecker !== null && $this->getRole() !== null) {
            return $this->authorizationChecker->isGranted($this->getRole());
        }

        return $visible;
    }

    /**
     * Return true if column is sorted.
     *
     * @return bool return true when column is sorted
     */
    public function isSorted()
    {
        return $this->isSorted;
    }

    public function setSortable($sortable)
    {
        $this->sortable = $sortable;

        return $this;
    }

    /**
     * column ability to sort.
     *
     * @return bool return true when column can be sorted
     */
    public function isSortable()
    {
        return $this->sortable;
    }

    /**
     * Return true if column is filtered.
     *
     * @return bool return true when column is filtered
     */
    public function isFiltered()
    {
        if ($this->hasFromOperandFilter()) {
            return true;
        }

        if ($this->hasToOperandFilter()) {
            return true;
        }

        return $this->hasOperatorFilter();
    }

    /**
     * @return bool
     */
    private function hasFromOperandFilter()
    {
        if (!isset($this->data['from'])) {
            return false;
        }

        if (!$this->isQueryValid($this->data['from'])) {
            return false;
        }

        return $this->data['from'] != static::DEFAULT_VALUE;
    }

    /**
     * @return bool
     */
    private function hasToOperandFilter()
    {
        if (!isset($this->data['to'])) {
            return false;
        }

        if (!$this->isQueryValid($this->data['to'])) {
            return false;
        }

        return $this->data['to'] != static::DEFAULT_VALUE;
    }

    /**
     * @return bool
     */
    private function hasOperatorFilter()
    {
        if (!isset($this->data['operator'])) {
            return false;
        }

        return $this->data['operator'] === Column::OPERATOR_ISNULL || $this->data['operator'] === Column::OPERATOR_ISNOTNULL;
    }

    /**
     * @param bool $filterable
     *
     * @return $this
     */
    public function setFilterable($filterable)
    {
        $this->filterable = $filterable;

        return $this;
    }

    /**
     * column ability to filter.
     *
     * @return bool return true when column can be filtred
     */
    public function isFilterable()
    {
        return $this->filterable;
    }

    /**
     * set column order.
     *
     * @param string $order asc|desc
     *
     * @return \APY\DataGridBundle\Grid\Column\Column
     */
    public function setOrder($order)
    {
        if ($order !== null) {
            $this->order = $order;
            $this->isSorted = true;
        }

        return $this;
    }

    /**
     * get column order.
     *
     * @return string asc|desc
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * Set column width.
     *
     * @param int $size in pixels
     *
     * @return \APY\DataGridBundle\Grid\Column\Column
     */
    public function setSize($size)
    {
        if ($size < -1) {
            throw new \InvalidArgumentException(sprintf('Unsupported column size %s, use positive value or -1 for auto resize', $size));
        }

        $this->size = $size;

        return $this;
    }

    /**
     * get column width.
     *
     * @return int column width in pixels
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * set filter data from session | request.
     *
     * @param  $data
     *
     * @return \APY\DataGridBundle\Grid\Column\Column
     */
    public function setData($data)
    {
        $this->data = ['operator' => $this->getDefaultOperator(), 'from' => static::DEFAULT_VALUE, 'to' => static::DEFAULT_VALUE];

        $hasValue = false;
        if (isset($data['from']) && $this->isQueryValid($data['from'])) {
            $this->data['from'] = $data['from'];
            $hasValue = true;
        }

        if (isset($data['to']) && $this->isQueryValid($data['to'])) {
            $this->data['to'] = $data['to'];
            $hasValue = true;
        }

        $isNullOperator = (isset($data['operator']) && ($data['operator'] === Column::OPERATOR_ISNULL || $data['operator'] === Column::OPERATOR_ISNOTNULL));
        if (($hasValue || $isNullOperator) && isset($data['operator']) && $this->hasOperator($data['operator'])) {
            $this->data['operator'] = $data['operator'];
        }

        return $this;
    }

    /**
     * get filter data from session | request.
     *
     * @return array data
     */
    public function getData()
    {
        $result = [];

        $hasValue = false;
        if (isset($this->data['from']) && $this->data['from'] != $this::DEFAULT_VALUE) {
            $result['from'] = $this->data['from'];
            $hasValue = true;
        }

        if (isset($this->data['to']) && $this->data['to'] != $this::DEFAULT_VALUE) {
            $result['to'] = $this->data['to'];
            $hasValue = true;
        }

        $isNullOperator = (isset($this->data['operator']) && ($this->data['operator'] === Column::OPERATOR_ISNULL || $this->data['operator'] === Column::OPERATOR_ISNOTNULL));
        if ($hasValue || $isNullOperator) {
            $result['operator'] = $this->data['operator'];
        }

        return $result;
    }

    /**
     * Return true if filter value is correct (has to be overridden in each Column class that can be filtered, in order to catch wrong values).
     *
     * @return bool
     */
    public function isQueryValid($query)
    {
        $callback = $this->getIsQueryValidCallback();

        if (!empty($callback)) {
            return call_user_func($callback, $query);
        }

        return true;
    }

    /**
     * Set column visibility for source class.
     *
     * @param $visibleForSource
     *
     * @return \APY\DataGridBundle\Grid\Column\Column
     */
    public function setVisibleForSource($visibleForSource)
    {
        $this->visibleForSource = $visibleForSource;

        return $this;
    }

    /**
     * Return true is column in visible for source class.
     *
     * @return bool
     */
    public function isVisibleForSource()
    {
        return $this->visibleForSource;
    }

    /**
     * Set column as primary.
     *
     * @param bool $primary
     *
     * @return $this
     */
    public function setPrimary($primary)
    {
        $this->primary = $primary;

        return $this;
    }

    /**
     * Return true is column in primary.
     *
     * @return bool
     */
    public function isPrimary()
    {
        return $this->primary;
    }

    /**
     * Set column align.
     *
     * @param string $align left/right/center
     *
     * @return $this
     */
    public function setAlign($align)
    {
        if (!in_array($align, Column::$aligns)) {
            throw new \InvalidArgumentException(sprintf('Unsupported align %s, just left, right and center are supported', $align));
        }

        $this->align = $align;

        return $this;
    }

    /**
     * get column align.
     *
     * @return bool
     */
    public function getAlign()
    {
        return $this->align;
    }

    public function setInputType($inputType)
    {
        $this->inputType = $inputType;

        return $this;
    }

    public function getInputType()
    {
        return $this->inputType;
    }

    public function setField($field)
    {
        $this->field = $field;

        return $this;
    }

    public function getField()
    {
        return $this->field;
    }

    public function setRole($role)
    {
        $this->role = $role;

        return $this;
    }

    public function getRole()
    {
        return $this->role;
    }

    /**
     * Filter.
     */
    public function setFilterType($filterType)
    {
        $this->filterType = strtolower($filterType);

        return  $this;
    }

    public function getFilterType()
    {
        return $this->filterType;
    }

    public function getFilters($source)
    {
        $filters = [];

        if (isset($this->data['operator']) && $this->hasOperator($this->data['operator'])) {
            if ($this instanceof ArrayColumn && in_array($this->data['operator'], [Column::OPERATOR_EQ, Column::OPERATOR_NEQ])) {
                $filters[] = new Filter($this->data['operator'], $this->data['from']);
            } else {
                switch ($this->data['operator']) {
                    case Column::OPERATOR_BTW:
                        if ($this->data['from'] != static::DEFAULT_VALUE) {
                            $filters[] = new Filter(Column::OPERATOR_GT, $this->data['from']);
                        }
                        if ($this->data['to'] != static::DEFAULT_VALUE) {
                            $filters[] = new Filter(Column::OPERATOR_LT, $this->data['to']);
                        }
                        break;
                    case Column::OPERATOR_BTWE:
                        if ($this->data['from'] != static::DEFAULT_VALUE) {
                            $filters[] = new Filter(Column::OPERATOR_GTE, $this->data['from']);
                        }
                        if ($this->data['to'] != static::DEFAULT_VALUE) {
                            $filters[] = new Filter(Column::OPERATOR_LTE, $this->data['to']);
                        }
                        break;
                    case Column::OPERATOR_ISNULL:
                    case Column::OPERATOR_ISNOTNULL:
                        $filters[] = new Filter($this->data['operator']);
                        break;
                    case Column::OPERATOR_LIKE:
                    case Column::OPERATOR_RLIKE:
                    case Column::OPERATOR_LLIKE:
                    case Column::OPERATOR_SLIKE:
                    case Column::OPERATOR_RSLIKE:
                    case Column::OPERATOR_LSLIKE:
                    case Column::OPERATOR_EQ:
                        if ($this->getSelectMulti()) {
                            $this->setDataJunction(Column::DATA_DISJUNCTION);
                        }
                    case Column::OPERATOR_NEQ:
                    case Column::OPERATOR_NLIKE:
                    case Column::OPERATOR_NSLIKE:
                        foreach ((array) $this->data['from'] as $value) {
                            $filters[] = new Filter($this->data['operator'], $value);
                        }
                        break;
                    default:
                        $filters[] = new Filter($this->data['operator'], $this->data['from']);
                }
            }
        }

        return $filters;
    }

    public function setDataJunction($dataJunction)
    {
        $this->dataJunction = $dataJunction;

        return $this;
    }

    /**
     * get data filter junction (how column filters are connected with column data).
     *
     * @return bool Column::DATA_CONJUNCTION | Column::DATA_DISJUNCTION
     */
    public function getDataJunction()
    {
        return $this->dataJunction;
    }

    public function setOperators(array $operators)
    {
        $this->operators = $operators;

        return $this;
    }

    /**
     * Return column filter operators.
     *
     * @return array $operators
     */
    public function getOperators()
    {
        // Issue with Doctrine
        // -------------------
        // @see http://www.doctrine-project.org/jira/browse/DDC-1857
        // @see http://www.doctrine-project.org/jira/browse/DDC-1858
        if ($this->hasDQLFunction() && version_compare(DoctrineVersion::VERSION, '2.5') < 0) {
            return array_intersect($this->operators, [Column::OPERATOR_EQ,
                Column::OPERATOR_NEQ,
                Column::OPERATOR_LT,
                Column::OPERATOR_LTE,
                Column::OPERATOR_GT,
                Column::OPERATOR_GTE,
                Column::OPERATOR_BTW,
                Column::OPERATOR_BTWE, ]);
        }

        return $this->operators;
    }

    public function setDefaultOperator($defaultOperator)
    {
        // @todo: should this be \InvalidArgumentException?
        if (!$this->hasOperator($defaultOperator)) {
            throw new \Exception($defaultOperator . ' operator not found in operators list.');
        }

        $this->defaultOperator = $defaultOperator;

        return $this;
    }

    public function getDefaultOperator()
    {
        return $this->defaultOperator;
    }

    /**
     * Return true if $operator is in $operators.
     *
     * @param string $operator
     *
     * @return bool
     */
    public function hasOperator($operator)
    {
        return in_array($operator, $this->operators);
    }

    public function setOperatorsVisible($operatorsVisible)
    {
        $this->operatorsVisible = $operatorsVisible;

        return $this;
    }

    public function getOperatorsVisible()
    {
        return $this->operatorsVisible;
    }

    public function setValues(array $values)
    {
        $this->values = $values;

        return $this;
    }

    public function getValues()
    {
        return $this->values;
    }

    public function setSelectFrom($selectFrom)
    {
        $this->selectFrom = $selectFrom;

        return $this;
    }

    public function getSelectFrom()
    {
        return $this->selectFrom;
    }

    public function getSelectMulti()
    {
        return $this->selectMulti;
    }

    public function setSelectMulti($selectMulti)
    {
        $this->selectMulti = $selectMulti;
    }

    public function getSelectExpanded()
    {
        return $this->selectExpanded;
    }

    public function setSelectExpanded($selectExpanded)
    {
        $this->selectExpanded = $selectExpanded;
    }

    public function hasDQLFunction(&$matches = null)
    {
        $regex = '/(?P<all>(?P<field>\w+):(?P<function>\w+)(:)?(?P<parameters>\w*))$/';

        return ($matches === null) ? preg_match($regex, $this->field) : preg_match($regex, $this->field, $matches);
    }

    /**
     * Internal function.
     *
     * @param $authorizationChecker
     *
     * @return $this
     */
    public function setAuthorizationChecker(AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->authorizationChecker = $authorizationChecker;

        return $this;
    }

    public function getParentType()
    {
        return '';
    }

    public function getType()
    {
        return '';
    }

    /**
     * By default all filers include a JavaScript onchange=submit block.  This
     * does not make sense in some cases, such as with multi-select filters.
     *
     * @todo Eventaully make this configurable via annotations?
     */
    public function isFilterSubmitOnChange()
    {
        return !$this->getSelectMulti();
    }

    public function setSearchOnClick($searchOnClick)
    {
        $this->searchOnClick = $searchOnClick;

        return $this;
    }

    public function getSearchOnClick()
    {
        return $this->searchOnClick;
    }

    /**
     * Allows to set twig escaping parameter (html, js, css, url, html_attr)
     * or to display raw value if type is false.
     *
     * @param string|bool $safeOption can be one of false, html, js, css, url, html_attr
     *
     * @return \APY\DataGridBundle\Grid\Column\Column
     */
    public function setSafe($safeOption)
    {
        $this->safe = $safeOption;

        return $this;
    }

    public function getSafe()
    {
        return $this->safe;
    }

    public function setSeparator($separator)
    {
        $this->separator = $separator;

        return $this;
    }

    public function getSeparator()
    {
        return $this->separator;
    }

    public function setJoinType($type)
    {
        $this->joinType = $type;

        return $this;
    }

    public function getJoinType()
    {
        return $this->joinType;
    }

    public function setExport($export)
    {
        $this->export = $export;

        return $this;
    }

    public function getExport()
    {
        return $this->export;
    }

    public function setClass($class)
    {
        $this->class = $class;

        return $this;
    }

    public function getClass()
    {
        return $this->class;
    }

    public function setIsManualField($isManualField)
    {
        $this->isManualField = $isManualField;
    }

    public function getIsManualField()
    {
        return $this->isManualField;
    }

    public function setIsAggregate($isAggregate)
    {
        $this->isAggregate = $isAggregate;
    }

    public function getIsAggregate()
    {
        return $this->isAggregate;
    }

    public function getUsePrefixTitle()
    {
        return $this->usePrefixTitle;
    }

    public function setUsePrefixTitle($usePrefixTitle)
    {
        $this->usePrefixTitle = $usePrefixTitle;

        return $this;
    }

    /**
     * Get TranslationDomain.
     *
     * @return string
     */
    public function getTranslationDomain()
    {
        return $this->translationDomain;
    }

    /**
     * Set TranslationDomain.
     *
     * @param string $translationDomain
     *
     * @return $this
     */
    public function setTranslationDomain($translationDomain)
    {
        $this->translationDomain = $translationDomain;

        return $this;
    }

    /**
     * @return array
     */
    public static function getAvailableOperators()
    {
        return Column::$availableOperators;
    }
}
