<?php

namespace Modules\Structure\Services;

use Doctrine\DBAL\Query\QueryException;
use App\Exceptions\ValidationException;
use App\Exceptions\ConstraintException;
use Illuminate\Database\Eloquent\Collection;
use Modules\Structure\Models\Department;
use Modules\Structure\Models\Organization;
use App\Contracts\BaseCrudServiceContract;
use App\Support\RequestOptions;

/**
 * Класс сервиса организаций.
 *
 * @package Modules\Structure\Services
 */
class OrganizationService implements BaseCrudServiceContract
{
	const ACCESS_VIEW_FULL           = 'tis.structure.view_full';
	const ACCESS_EDIT_FULL           = 'tis.structure.edit_full';
	const ACCESS_EDIT_OWN_DEPARTMENT = 'tis.structure.edit_own_department';

	/**
	 * Правила валидации
	 *
	 * @var array
	 */
	protected $rules = [
		'short_name' => 'required|min:2|max:255',
		'full_name'  => 'required|min:2|max:255',
	];

	/**
	 * Получить список организаций.
	 *
	 * @param RequestOptions $options
	 * @return Organization[]|Collection
	 */
	public function getAll(RequestOptions $options)
	{
		//TODO Проверка прав

		return Organization::applyOptions($options)->get();
	}

	/**
	 * Получить организацию.
	 *
	 * @param int $id
	 * @param RequestOptions $options
	 * @return Organization
	 */
	public function getOne($id, RequestOptions $options = null)
	{
		//TODO Проверка прав

		return Organization::applyOptions($options)->firstOrFail($id);
	}

	/**
	 * Создать организацию.
	 *
	 * @param RequestOptions $options
	 * @return Organization
	 * @throws \Exception
	 */
	public function create(RequestOptions $options)
	{
		//TODO Проверка прав

		$organizationTable = Organization::getModel()->getTable();
		$this->rules['short_name'] .= '|unique:' . $organizationTable;
		$this->rules['full_name']  .= '|unique:' . $organizationTable;
		$data = $options->getData();
		Organization::validate($data, $this->rules);

		$organization = null;
		try {
			\DB::beginTransaction();

			// Создание организации (всегда юридическое лицо)
			$data['is_legal_entity'] = true;
			$organization = Organization::create($data);

			// Создание корня в дереве отделов
			$rootDepartment = Department::createRoot($organization);
			$rootDepartment->children = [];
			$organization->departments = $rootDepartment;
			\DB::commit();
		} catch (\Exception $e) {
			\DB::rollBack();
			throw $e;
		}

		return $organization;
	}

	/**
	 * Обновить организацию.
	 *
	 * @param int $id
	 * @param RequestOptions $options
	 * @return Organization
	 * @throws ValidationException
	 */
	public function update($id, RequestOptions $options)
	{
		//TODO Проверка прав

		$organizationTable = Organization::getModel()->getTable();
		$this->rules['short_name'] .= '|unique:' . $organizationTable . ',short_name,' . $id;
		$this->rules['full_name']  .= '|unique:' . $organizationTable . ',full_name,' . $id;
		Organization::validate($options->getData(), $this->rules);

		$organization = Organization::findOrFail($id);
		$organization->update($options->getData());

		return Organization::applyOptions($options)->findOrFail($id);
	}

	/**
	 * Удалить организацию.
	 *
	 * @param int $id
	 * @return Organization
	 * @throws ConstraintException
	 */
	public function destroy($id)
	{
		//TODO Проверка прав

		/** @var Organization $organization */
		$organization = Organization::findOrFail($id);

		try {
			\DB::beginTransaction();

			// Удаление корневого отдела организации
			$rootDepartment = Department::getRootDepartment($organization);
			if ($rootDepartment->isLeaf()) {
				$rootDepartment->delete();
				$organization->delete();

				\DB::commit();
			} else {
				\DB::rollBack();
				throw new ConstraintException('Удаление не разрешено, т.к. организация содержит отделы');
			}

		} catch (QueryException $e) {
			\DB::rollBack();
		}

		return $organization;
	}
}

