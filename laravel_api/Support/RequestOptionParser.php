<?php

namespace App\Support;

use Illuminate\Http\Request;
use App\Support\RequestOptions;

class RequestOptionParser
{
    /**
     * @var \App\Support\RequestOptions
     */
    protected $options = [];

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var array соответствие преобразований операций
     */
    protected $operations = [
        'eq'     => '=',
        'ne'     => '!=',
        'gt'     => '>',
        'ge'     => '>=',
        'lt'     => '<',
        'le'     => '<=',
        'like'   => 'LIKE',
        'in'     => 'IN',
        'not_in' => 'NOT_IN',
    ];

    /**
     * Параметры, которые могут иметь значение null.
     * Значение 'null' будет преобразовано в null.
     *
     * @var array
     */
    protected $castsNullable = [];

    /**
     * Параметры, которые должны иметь логический тип.
     * Значение '1', 'true', 'on' и 'yes' будет преобразовано в TRUE, иначе в FALSE.
     *
     * @var array
     */
    protected $castsBoolean = [];

    /**
     * Ошибки при парсинге
     *
     * @var array
     */
    protected $errors = [];

    public function __construct(Request $request)
    {
        $this->options = new RequestOptions();
        $this->request = $request;
    }

    /**
     * Распарсить запрос.
     *
     * @return \App\Support\RequestOptions
     */
    public function get()
    {
        $this->options
            ->setData($this->parseData($this->request->input()))
            ->setColumns($this->request->get('columns', []))
            ->setIncludes($this->request->get('includes', []))
            ->setFilters($this->parseFilters($this->request->get('filters', [])))
            ->setSorters($this->parseSorters($this->request->get('sorters', [])))
            ->setOffset($this->request->get('offset', 0))
            ->setLimit($this->request->get('limit', 1000));

        if (!empty($this->errors)) {
            $this->options->setErrors($this->errors);
        }

        return $this->options;
    }

    /**
     * Вернуть данные, исключив опции.
     *
     * @param array $data
     * @return array
     */
    protected function parseData(array $data)
    {
        $data = array_diff_key($data, array_flip(['columns', 'includes', 'sorters', 'filters', 'offset', 'limit']));

        // Спарсить пустую строку или значение null в виде текста
        foreach ($this->castsNullable as $field) {
            if (isset($data[$field])) {
                $data[$field] = (
                    ($data[$field] === '' || strtolower($data[$field]) == 'null') ? null : $data[$field]
                );
            }
        }

        // Спарсить логическое значение в виде текста
        foreach ($this->castsBoolean as $field) {
            if (isset($data[$field])) {
                $data[$field] = $this->convertBoolStrToInt($data[$field]);
            }
        }

        return $data;
    }

    /**
     * Разобрать настройки сортировки.
     *
     * <pre>
     * [
     *   {
     *     key: 'field_name',
     *     direction: 'ASC|DESC'
     *   },
     *   ...
     * ]
     * </pre>
     *
     * @param array $sorters
     * @return array
     */
    protected function parseSorters(array $sorters)
    {
        $result = [];

        if (empty($sorters)) {
            return $result;
        }

        foreach ($sorters as $sorter) {
            if (empty($sorter['key'])) {
                $this->errors[] = 'Поле сортировки не задано';
                continue;
            }

            $sorter['direction'] = strtoupper($sorter['direction'] ?? 'ASC');
            if (!in_array($sorter['direction'], ['ASC', 'DESC'])) {
                $sorter['direction'] = 'ASC';
            }

            $result[] = $sorter;
        }

        return $result;
    }

    /**
     * Разобрать настройки фильтров.
     *
     * <pre>
     * [
     *   {
     *     key: 'field_name',
     *     value: 'value',
     *     operation: 'eq'
     *   },
     *   ...
     * ]
     * </pre>
     *
     * @param array $filters
     * @return array
     */
    protected function parseFilters(array $filters)
    {
        $result = [];

        if (empty($filters)) {
            return $result;
        }

        foreach ($filters as $filter) {
            if (empty($filter['key'])) {
                $this->errors[] = 'Поле фильтра не задано';
                continue;
            }

            if (!isset($filter['value'])) {
                $this->errors[] = 'Значение фильтра не задано';
                continue;
            } elseif (in_array($filter['key'], $this->castsBoolean)) {
                $filter['value'] = $this->convertBoolStrToInt($filter['value']);
            }

            if (empty($filter['operation']) || !array_key_exists($filter['operation'], $this->operations)) {
                $filter['operation'] = 'eq';
            }

            if ($filter['operation'] == 'like') {
                $filter['value'] = '%' . $filter['value'] . '%';
            }

            $filter['operation'] = $this->operations[$filter['operation']];

            $result[] = $filter;
        }

        return $result;
    }

    /**
     * Преобразовать логическое значение в текстовом виде в целочисленное.
     *
     * @param string $value
     * @return int
     */
    protected function convertBoolStrToInt($value)
    {
        return (int) filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }
}
