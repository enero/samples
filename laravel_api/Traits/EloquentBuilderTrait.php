<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use App\Support\RequestOptions;

/**
 * Типаж для примения опций к конструктору запросов.
 *
 * @package App\Traits
 */
trait EloquentBuilderTrait
{
	/**
	 * @var array разделы настроек запроса
	 */
	private $requestSections = [
		'columns',
		'includes',
		'sorters',
		'filters',
		'limit',
		'offset',
	];

	/**
	 * Применить настройки выборки.
	 *
	 * @param Builder|QueryBuilder $query
	 * @param RequestOptions $options
	 * @return Builder|QueryBuilder
	 */
	public function applyResourceOptions($query, RequestOptions $options = null)
	{
		foreach ($this->requestSections as $section) {
			$section     = ucfirst($section);
			$getMethod   = 'get' . $section;
			$applyMethod = 'apply' . $section;
			$optionData  = (method_exists($options, $getMethod) ? $options->{$getMethod}() : null);

			if (!empty($optionData) && method_exists($this, $applyMethod)) {
				call_user_func_array([$this, $applyMethod], [$query, $optionData]);
			}
		}

		return $query;
	}

	/**
	 * Установить поля выборки.
	 *
	 * @param Builder|QueryBuilder $query
	 * @param array $columns
	 */
	public function applyColumns($query, $columns)
	{
		$query->select($columns);
	}

	/**
	 * Установить включение отношений.
	 *
	 * @param Builder|QueryBuilder $query
	 * @param array $includes
	 */
	public function applyIncludes($query, $includes)
	{
		$query->with($includes);
	}

	/**
	 * Установить сортировку.
	 *
	 * @param Builder|QueryBuilder $query
	 * @param array $sorters
	 */
	public function applySorters($query, $sorters)
	{
		foreach ($sorters as $sorter) {
			$query->orderBy($sorter['key'], $sorter['direction']);
		}
	}

	/**
	 * Установить фильтры.
	 *
	 * @param Builder|QueryBuilder $query
	 * @param $filters
	 */
	public function applyFilters($query, $filters)
	{
		foreach ($filters as $filter) {
			$alias = (!empty($filter['alias']) ? $filter['alias'] . '.' : '');
			if (strpos($filter['key'], '.') !== false) {
				// Ключ с вложениями
				$included = explode('.', $filter['key']);

				$depth = count($included) - 1;
				$index = 0;
				// Рекурсивно применяется whereHas для отношений, пока не достигнет поля, к которому применяется where
				$whereHasCallback = function (Builder $query) use (&$whereHasCallback, &$index, $depth, $included, $filter, $alias) {
					$index++;
					// При запросах, использующих разные схемы
					$query->from($query->getModel()->getTableWithSchema());
					if ($index == $depth) {
						switch ($filter['operation']) {
							case 'IN':
								$query->whereIn($alias . $included[$index], $filter['value']);
								break;
							case 'NOT_IN':
								$query->whereNotIn($alias . $included[$index], $filter['value']);
								break;
							default:
								$query->where($alias . $included[$index], $filter['operation'], $filter['value']);
								break;
						}
					} else {
						$query->whereHas($included[$index], $whereHasCallback);
					}
				};

				$tableNameWithSchema = $query->getModel()->getTableWithSchema();
				$query->from($tableNameWithSchema)->whereHas($included[$index], $whereHasCallback);
			} else {
				switch ($filter['operation']) {
					case 'IN':
						$query->whereIn($alias . $filter['key'], $filter['value']);
						break;
					case 'NOT_IN':
						$query->whereNotIn($alias . $filter['key'], $filter['value']);
						break;
					default:
						$query->where($alias . $filter['key'], $filter['operation'], $filter['value']);
						break;
				}
			}
		}
	}

	/**
	 * Установить лимит выборки.
	 *
	 * @param Builder|QueryBuilder $query
	 * @param integer $limit
	 */
	public function applyLimit($query, $limit)
	{
		$query->limit($limit);
	}

	/**
	 * Указать смещение выборки.
	 *
	 * @param Builder|QueryBuilder $query
	 * @param integer $offset
	 */
	public function applyOffset($query, $offset)
	{
		$query->offset($offset);
	}
}
