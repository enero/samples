<?php

namespace App\Traits;

use App\Models\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Validator;
use App\Exceptions\ValidationException;
use App\Support\RequestOptions;

/**
 * Типаж для свойств и методов базовой модели.
 *
 * @package App\Traits
 */
trait ModelTrait
{
	/**
	 * Сравнить на идентичность две модели.
	 *
	 * @param Model $model
	 * @return bool
	 */
	public function is(Model $model)
	{
		$equalType       = (($this instanceOf $model) || ($model instanceOf $this));
		$equalPrimaryKey = ($this->getKey() == $model->getKey());

		return ($equalType && $equalPrimaryKey);
	}

	/**
	 * Получить полное название поля ({схема}.{таблица}.{поле}).
	 *
	 * @return string
	 */
	public function getQualifiedKeyName()
	{
		return $this->getTableWithSchema() . '.' . $this->getKeyName();
	}

	/**
	 * Получить полное название таблицы ({схема}.{таблица}).
	 *
	 * @return string
	 */
	public function getTableWithSchema()
	{
		return $this->getConnectionName() . '.' . $this->getTable();
	}

	/**
	 * Сформировать SQL-строку с подставленными значениями $binding.
	 *
	 * @param Builder $builder
	 * @return string
	 */
	public static function formatBuilder(Builder $builder)
	{
		$sql = $builder->toSql();
		foreach ($builder->getBindings() as $binding) {
			$value = is_numeric($binding) ? $binding : "'".$binding."'";
			$sql = preg_replace('/\?/', $value, $sql, 1);
		}

		return $sql;
	}

	/**
	 * Проверить данные.
	 *
	 * @param array $data
	 * @param null  $rules
	 * @param array $messages
	 * @param array $customAttributes
	 * @return bool
	 * @throws ValidationException
	 */
	public static function validate(array $data = [], $rules = null, array $messages = [], array $customAttributes = [])
	{
		$validator = Validator::make($data, $rules, $messages, $customAttributes);

		if ($validator->fails()) {
			throw new ValidationException($validator);
		}

		return true;
	}

	/**
	 * Применить настройки к выборке и вернуть её.
	 *
	 * @param RequestOptions $options
	 * @param Builder|QueryBuilder|null $query
	 * @return Builder|QueryBuilder|static
	 */
	public static function applyOptions(RequestOptions $options, $query = null)
	{
		$model = new static();

		if (!$query) {
			$query = $model->query();
		}

		$model->applyResourceOptions($query, $options);

		return $query;
	}
}
