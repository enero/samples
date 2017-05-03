<?php

namespace Modules\Structure\Services;

use Illuminate\Database\Eloquent\Collection;
use Modules\Structure\Models\Grade;
use Modules\Structure\Models\Position;
use App\Contracts\BaseCrudServiceContract;
use App\Support\RequestOptions;

/**
 * Класс сервиса должностей.
 *
 * @package Modules\Structure\Services
 */
class PositionService implements BaseCrudServiceContract
{
	/**
	 * Получить правила валидации.
	 *
	 * @param Position|null $position
	 * @return array
	 */
	public static function getRules(Position $position = null)
	{
		$gradeScheme = (new Grade())->getTableWithSchema();

		return [
			'name'     => 'required|min:2|max:255',
			'grade_id' => 'numeric|exists:' . $gradeScheme . ',id'
		];
	}

	/**
	 * Получить список должностей.
	 *
	 * @param RequestOptions $options
	 * @return Collection|Position[]
	 */
	public function getAll(RequestOptions $options)
	{
		return Position::applyOptions($options)->get();
	}

	/**
	 * Получить должность.
	 *
	 * @param int $id
	 * @param RequestOptions $options
	 * @return Position
	 */
	public function getOne($id, RequestOptions $options = null)
	{
		return Position::applyOptions($options)->findOrFail($id);
	}

	/**
	 * Создать должность.
	 *
	 * @param RequestOptions $options
	 * @return Position
	 * @throws \App\Exceptions\ValidationException
	 */
	public function create(RequestOptions $options = null)
	{
		Position::validate($options->getData(), static::getRules());

		$position = Position::create($options->getData());

		return Position::applyOptions($options)->findOrFail($position->id);
	}

	/**
	 * Обновить должность.
	 *
	 * @param int $id
	 * @param RequestOptions $options
	 * @return Position
	 * @throws \App\Exceptions\ValidationException
	 */
	public function update($id, RequestOptions $options)
	{
		$position = Position::findOrFail($id);

		Position::validate($options->getData(), static::getRules($position));

		$position->update($options->getData());

		return Position::applyOptions($options)->findOrFail($id);
	}

	/**
	 * Удалить должность.
	 *
	 * @param int $id
	 * @return Position
	 */
	public function destroy($id)
	{
		$position = Position::findOrFail($id);
		$position->delete();

		return $position;
	}
}
