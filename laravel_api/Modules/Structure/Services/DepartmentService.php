<?php

namespace Modules\Structure\Services;

use Illuminate\Database\Eloquent\Collection;
use App\Support\RequestOptions;
use App\Models\Core\User;
use Modules\Structure\Models\Department;
use Modules\Structure\Models\Organization;
use Modules\Structure\Models\Staff;
use Baum\Extensions\Eloquent\Collection as BaumCollection;
use App\Contracts\BaseCrudServiceContract;
use App\Exceptions\ValidationException;

/**
 * Класс сервиса отделов.
 *
 * @package Modules\Structure\Services
 */
class DepartmentService implements BaseCrudServiceContract
{
	/**
	 * Базовые правила валидации
	 *
	 * @var array
	 */
	protected $rules = [
		'organization_id' => 'required|integer',
		'name'            => 'required|min:2|max:255',
		'code'            => 'required',
		'max_staff'       => 'required|integer|min:1',
	];

	/**
	 * Получить корневой отдел вместе с дочерними.
	 *
	 * @param RequestOptions $options
	 * @return Department
	 * @throws ValidationException
	 */
	public function getAll(RequestOptions $options)
	{
		//TODO Проверка прав

		Department::validate($options->getData(), ['organization_id' => 'required|integer']);

		return Department::getDescendantsAndSelfDepartment($options)->toHierarchy()->first();
	}

	/**
	 * Получить отдел вместе с дочерними.
	 *
	 * @param int $id
	 * @param RequestOptions $options
	 * @return Department
	 */
	public function getOne($id, RequestOptions $options = null)
	{
		//TODO Проверка прав

		/** @var Department $department */
		$department = Department::findOrFail($id);

		$query = $department->descendantsAndSelf();
		$department->applyOptions($options, $query);

		/** @var BaumCollection $departments */
		$departments = $query->get();

		return $departments->toHierarchy()->first();
	}

	/**
	 * Создать отдел.
	 *
	 * @param RequestOptions $options
	 * @return Department
	 * @throws ValidationException
	 */
	public function create(RequestOptions $options)
	{
		//TODO Проверка прав

		$this->rules['code'] .= '|unique:' . Department::getModel()->getTable();
		Department::validate($options->getData(), $this->rules);

		return Department::createNode($options->getData());
	}

	/**
	 * Обновить отдел.
	 *
	 * @param int $id
	 * @param RequestOptions $options
	 * @return Department
	 * @throws ValidationException
	 */
	public function update($id, RequestOptions $options)
	{
		//TODO Проверка прав

		$this->rules['code']      .= '|unique:' . Department::getModel()->getTable() . ',code,' . $id;
		$this->rules['max_staff'] .= '|greaterOrEqualThenAssignedUsersCount';
		Department::validate($options->getData(), $this->rules);

		/** @var Department $department */
		$department = Department::findOrFail($id);
		$department->update($options->getData());

		return $department;
	}

	/**
	 * Обновить дерево отделов.
	 *
	 * @param int $id идентификатор корневого отдела
	 * @param RequestOptions $options
	 * @return Department[]|Collection
	 */
	public function updateTree($id, RequestOptions $options)
	{
		//TODO Проверка прав

		$data = $options->getData();
		if (!empty($options->getData()['children'])) {
			$model = new Department();

			$data = $this->filterAndValidateTree(
				$data,
				array_flip(array_merge(['id'], $model->getFillable()))
			);
		} else {
			// Если отсутствует ключ children, то считается, что это удаление всего дерева отделов организации
			$data['children'] = [];
		}

		$department = Department::updateTree($id, $data['children']);

		return $department->getDescendantsAndSelfDepartment($options->setData($data))->toHierarchy()->first();
	}

	/**
	 * Удалить отдел.
	 *
	 * @param int $id
	 * @return Department
	 */
	public function destroy($id)
	{
		//TODO Проверка прав

		/** @var Department $department */
		$department = Department::findOrFail($id);
		$department->delete();

		return $department;
	}

	/**
	 * Очистить дерево от лишних параметров и сделать проверку.
	 *
	 * @param array $data
	 * @param array $fillable
	 * @return array
	 */
	private function filterAndValidateTree(array $data, array $fillable)
	{
		$rules = $this->rules;
		$rules['code'] .= '|unique:' . Department::getModel()->getTable();

		$processChildren = function(array $data) use (&$processChildren, $fillable, $rules) {
			foreach ($data as &$node) {
				$children = $node['children'] ?? [];
				$node = array_intersect_key($node, $fillable);

				// $currentRules = clone $rules;
				$currentRules = array_merge_recursive([], $rules);
				if (isset($node['id']) && is_numeric($node['id'])) {
					$currentRules['code']      .= ',code,' . $node['id'];
					$currentRules['max_staff'] .= '|greaterOrEqualThenAssignedUsersCount';
				} else {
					// При добавлении с клиента приходит значение типа _TEMP_1234567
					unset($node['id']);
				}
				Department::validate($node, $currentRules);

				if (!empty($children)) {
					$node['children'] = $processChildren($children);
				}
			}

			return $data;
		};
		$data['children'] = $processChildren($data['children'], $fillable);

		return $data;
	}

	/**
	 * Проверить уникальность кода.
	 *
	 * @param RequestOptions $options
	 * @return bool
	 */
	public function isUniqueCode(RequestOptions $options = null)
	{
		//TODO Проверка прав

		$rules['code'] = 'required|unique:' . Department::getModel()->getTable();
		if (!empty($options->getData()['id'])) {
			$rules['id']    = 'integer';
			$rules['code'] .= ',code,' . $options->getData()['id'];
		}

		try {
			Department::validate($options->getData(), $rules);
		} catch (ValidationException $e) {
			return false;
		}

		return true;
	}

	/**
	 * Получить отдел, в котором состоит пользователь, для определения положения по умолчанию при входе в орг. структуру.
	 *
	 * @param User $user
	 * @return BaumCollection
	 */
	public function getDefaultDepartment(User $user)
	{
		$staffTable         = app(Staff::class)->getTable();
		$departmentsTable   = app(Department::class)->getTable();
		$organizationsTable = app(Organization::class)->getTable();

		/** @var Department $defaultDepartment */
		$defaultDepartment = Department::query()
			->join($staffTable, $departmentsTable . '.id', '=', $staffTable . '.department_id')
			->join($organizationsTable, $departmentsTable . '.organization_id', '=', $organizationsTable . '.id')
			->where($staffTable . '.user_id', $user->id)
			->where($staffTable . '.is_main_job', true)
			->where($organizationsTable . '.is_legal_entity', false)
			->first();

		if (empty($defaultDepartment)) {
			return null;
		}

		return $defaultDepartment->getDescendantsAndSelf()->toHierarchy()->first();
	}
}
