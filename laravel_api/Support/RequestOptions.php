<?php

namespace App\Support;

/**
 * Class RequestOptions
 *
 * @package App\Support
 */
class RequestOptions
{
    protected $data     = [];
    //TODO columns не будет корректно отрабатывать, т.к. несмотря на то, что в выборке поля отфильтрованы, в трансформере остальные поля добавляются с пустыми значениями. Либо удалить, либо доработать.
    protected $columns  = [];
    //TODO includes некорректно работают с деревом
    protected $includes = [];
    protected $sorters  = [];
    protected $filters  = [];
    protected $offset   = 0;
    protected $limit    = 1000;
    protected $errors   = [];

    /**
     * @param array $data
     *
     * @return $this
     */
    public function setData(array $data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param array $column
     *
     * @return $this
     */
    public function addColumn(array $column)
    {
        $this->columns[] = $column;

        return $this;
    }

    /**
     * @param array $columns
     *
     * @return $this
     */
    public function setColumns(array $columns)
    {
        $this->columns = $columns;

        return $this;
    }

    /**
     * @return array
     */
    public function getColumns()
    {
        return $this->columns;
    }

    /**
     * @param array $include
     *
     * @return $this
     */
    public function addInclude(array $include)
    {
        $this->includes[] = $include;

        return $this;
    }

    /**
     * @param array $includes
     *
     * @return $this
     */
    public function setIncludes(array $includes)
    {
        $this->includes = $includes;

        return $this;
    }

    /**
     * @return array
     */
    public function getIncludes()
    {
        return $this->includes;
    }

    /**
     * @param array $sorter
     *
     * @return $this
     */
    public function addSorter(array $sorter)
    {
        $this->sorters[] = $sorter;

        return $this;
    }

    /**
     * @param array $sorters
     *
     * @return $this
     */
    public function setSorters(array $sorters)
    {
        $this->sorters = $sorters;

        return $this;
    }

    /**
     * @return array
     */
    public function getSorters()
    {
        return $this->sorters;
    }

    /**
     * @param array $filter
     *
     * @return $this
     */
    public function addFilter(array $filter)
    {
        $this->filters[] = $filter;

        return $this;
    }

    /**
     * @param array $filters
     *
     * @return $this
     */
    public function setFilters(array $filters)
    {
        $this->filters = $filters;

        return $this;
    }

    /**
     * @return array
     */
    public function getFilters()
    {
        return $this->filters;
    }

    /**
     * @param int $offset
     *
     * @return $this
     */
    public function setOffset(int $offset)
    {
        $this->offset = $offset;

        return $this;
    }

    /**
     * @return int
     */
    public function getOffset()
    {
        return $this->offset;
    }

    /**
     * @param int $limit
     *
     * @return $this
     */
    public function setLimit(int $limit)
    {
        $this->limit = $limit;

        return $this;
    }

    /**
     * @return int
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * @param array $errors
     *
     * @return $this
     */
    public function setErrors(array $errors = [])
    {
        $this->errors = $errors;

        return $this;
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }
}
