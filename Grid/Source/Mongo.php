<?php
namespace APY\DataGridBundle\Grid\Source;

use APY\DataGridBundle\Grid\Column\BooleanColumn;
use APY\DataGridBundle\Grid\Column\Column;
use APY\DataGridBundle\Grid\Column\ColumnInterface;
use APY\DataGridBundle\Grid\Row;
use APY\DataGridBundle\Grid\Rows;
use Doctrine\Persistence\ObjectManager;
use MongoDB\BSON\ObjectId;
use MongoDB\BSON\Regex;
use MongoDB\BSON\UTCDateTime;

/**
 * mongo documents treated as arrays
 */
class Mongo extends Source
{
    protected $parameters;

    protected $mongoClient;
    protected $mongoDatabase;
    protected $mongoCollection;
    protected $columnService;

    protected $currentSearch = [];
    protected $count = 0;

    public function __construct(Column $columnService, ObjectManager $documentManager)
    {
        $this->columnService = $columnService;
        $this->documentManager = $documentManager;
        $this->mongoClient = $documentManager->getClient();
    }

    public function setup(array $parameters)
    {
        if (!isset($parameters["database"]) || empty($parameters["database"])) {
            throw new \Exception("Mongo source database has to be set.");
        }

        if (!isset($parameters["collection"]) || empty($parameters["collection"])) {
            throw new \Exception("Mongo source collection has to be set.");
        }

        $this->parameters = $parameters;
        $this->mongoDatabase = $this->mongoClient->selectDatabase($parameters["database"]);
        $this->mongoCollection = $this->mongoClient->selectCollection($this->mongoDatabase, $parameters["collection"]);
    }

    public function initialise()
    {
        // nothing to do here
    }

    /**
     * @param \APY\DataGridBundle\Grid\Columns $columns
     */
    public function getColumns($columns)
    {
        // not needed
    }

    public function execute($columns, $page = 0, $limit = 0, $maxResults = null, $gridDataJunction = Column::DATA_CONJUNCTION)
    {
        $validColumns = [];
        $search = [];
        $searchParams = [];

        foreach ($columns as $column) {
            if ($column->isSorted()) {
                if (!isset($searchParams["sort"])) {
                    $searchParams["sort"] = [];
                    $searchParams["sort"][$column->getField()] = $column->getOrder() === "desc" ? -1 : 1;
                }
            }

            if ($column->isPrimary()) {
                $column->setFilterable(false);
            } elseif ($column->isFiltered()) {
                // Some attributes of the column can be changed in this function
                $filters = $column->getFilters('mongo');

                $columnFilters = [];
                foreach ($filters as $filter) {
                    //normalize values
                    $operator = $this->normalizeOperator($filter->getOperator());
                    $value = $this->normalizeValue($filter->getOperator(), $filter->getValue(), $column);
                    $columnFilters[] = [$operator => $value];
                }

                if (count($columnFilters) > 1) {
                    $junction = ($column->getDataJunction() === Column::DATA_DISJUNCTION) ? '$or' : '$and';
                    $hlp = [];
                    foreach ($columnFilters as $columnFilter) {
                        $hlp[] = [$column->getField() => $columnFilter];
                    }
                    $search[$junction] = $hlp;
                } else {
                    $columnFilters = reset($columnFilters);
                    $search[$column->getField()] = $columnFilters;
                }
            }

            $validColumns[] = $column;
        }

        $searchParams["projection"] = [];
        foreach ($validColumns as $column) {
            $searchParams["projection"][$column->getField()] = 1;
        }

        if (count($search) > 1 && $gridDataJunction === Column::DATA_DISJUNCTION) {
            // or
            $hlp = [];
            foreach ($search as $key => $val) {
                $hlp[] = [$key => $val];
            }
            $search = ['$or' => $hlp];
        }

        // can be changed by callback
        $res = $this->prepareQuery([
            "filter" => $search,
            "parameters" => $searchParams,
        ]);
        $search = $res["filter"];
        $searchParams = $res["parameters"];

        $this->currentSearch = $search;

        dump($search);

        $this->count = $this->mongoCollection->count($search, $searchParams);

        if ($page > 0) {
            $searchParams["skip"] = $page * $limit;
        }

        if ($limit > 0) {
            if ($maxResults !== null && ($maxResults - $page * $limit < $limit)) {
                $limit = $maxResults - $page * $limit;
            }

            $searchParams["limit"] = (int) $limit;
        } elseif ($maxResults !== null) {
            $searchParams["limit"] = (int) $maxResults;
        }

        //execute and get results
        $result = new Rows();

        $cursor = $this->mongoCollection->find($search, $searchParams);

        foreach ($cursor as $record) {
            $record = $this->untransformRecord($record);
            $row = new Row();

            foreach ($validColumns as $column) {
                $columnId = $column->getId();
                if (isset($record[$columnId])) {
                    $row->setField($columnId, $record[$columnId]);
                }
            }

            // call overridden prepareRow or associated closure
            if (($modifiedRow = $this->prepareRow($row)) !== null) {
                $result->addRow($modifiedRow);
            }
        }

        return $result;
    }

    protected function untransformRecord(array $record)
    {
        if (isset($record["_id"]) && !isset($record["id"])) {
            $record["id"] = $record["_id"];
        }

        foreach ($record as $index => $value) {
            $record[$index] = $this->untransformRecordValue($value);
        }

        return $record;
    }

    protected function untransformRecordValue($value)
    {
        if ($value instanceof ObjectId) {
            $value = (string) $value;
        }

        if ($value instanceof UTCDateTime) {
            $value = $value->toDateTime();
        }

        return $value;
    }

    protected function normalizeOperator($operator)
    {
        switch ($operator) {
            // For case insensitive
            case Column::OPERATOR_EQ:
                return '$eq';
            case Column::OPERATOR_NEQ:
                return '$ne';
            case Column::OPERATOR_LT:
                return '$lt';
            case Column::OPERATOR_LTE:
                return '$lte';
            case Column::OPERATOR_GT:
                return '$gt';
            case Column::OPERATOR_GTE:
                return '$gte';
            case Column::OPERATOR_LIKE:
            case Column::OPERATOR_NLIKE:
            case Column::OPERATOR_RLIKE:
            case Column::OPERATOR_LLIKE:
            case Column::OPERATOR_SLIKE:
            case Column::OPERATOR_NSLIKE:
            case Column::OPERATOR_RSLIKE:
            case Column::OPERATOR_LSLIKE:
                return '$regex';
            case Column::OPERATOR_ISNULL:
            case Column::OPERATOR_ISNOTNULL:
                return '$exists';
            default:
                return $operator;
        }
    }

    protected function normalizeValue($operator, $value, ColumnInterface $column = null)
    {
        if ($column && $column instanceof BooleanColumn) {
            $value = (boolean) $value;
            return $value;
        }

        switch ($operator) {
            case Column::OPERATOR_LIKE:
                return new Regex($value, 'i');
            case Column::OPERATOR_NLIKE:
                return new Regex('^((?!' . $value . ').)*$', 'i');
            case Column::OPERATOR_RLIKE:
                return new Regex('^' . $value, 'i');
            case Column::OPERATOR_LLIKE:
                return new Regex($value . '$', 'i');
            case Column::OPERATOR_SLIKE:
                return new Regex($value, '');
            case Column::OPERATOR_RSLIKE:
                return new Regex('^' . $value, '');
            case Column::OPERATOR_LSLIKE:
                return new Regex($value . '$', '');
            case Column::OPERATOR_ISNULL:
                return false;
            case Column::OPERATOR_ISNOTNULL:
                return true;
            default:
                if ($value instanceof \DateTime) {
                    $value = new UTCDateTime($value);
                }

                return $value;
        }
    }

    public function populateSelectFilters($columns, $loop = false)
    {
        foreach ($columns as $column) {
            $selectFrom = $column->getSelectFrom();

            if ($column->getFilterType() === 'select' && ($selectFrom === 'source' || $selectFrom === 'query')) {

                // For negative operators, show all values
                if ($selectFrom === 'query') {
                    foreach ($column->getFilters('mongo') as $filter) {
                        if (in_array($filter->getOperator(), [Column::OPERATOR_NEQ, Column::OPERATOR_NLIKE, Column::OPERATOR_NSLIKE])) {
                            $selectFrom = 'source';
                            break;
                        }
                    }
                }

                // Dynamic from query or not ?
                $search = ($selectFrom === 'source') ? [] : $this->currentSearch;

                $field = $column->getField();
                $results = $this->mongoCollection->distinct($field, $search);

                $values = [];
                foreach ($results as $value) {
                    $values[] = $this->untransformRecordValue($value);
                }

                sort($values);
                $column->setValues($values);
            }
        }
    }

    public function getTotalCount($maxResults = null)
    {
        if ($maxResults !== null) {
            return min([$maxResults, $this->count]);
        }

        return $this->count;
    }

    public function getHash()
    {
        $hlp = [];
        if (isset($this->parameters["database"])) {
            $hlp[] = $this->parameters["database"];
        }
        if (isset($this->parameters["collection"])) {
            $hlp[] = $this->parameters["collection"];
        }

        return __CLASS__ . md5(implode("", $hlp));
    }

    public function delete(array $ids)
    {
        if (!empty($ids)) {
            $ids = array_map(function($a) {
                return new ObjectId($a);
            }, $ids);

            $this->mongoCollection->deleteMany(['_id' => ['$in' => $ids]]);
        }
    }
}
