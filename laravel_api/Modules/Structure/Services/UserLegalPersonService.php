<?php

namespace Modules\Structure\Services;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Support\RequestOptions;
use Modules\Structure\Models\Department;
use Modules\Structure\Models\Organization;
use Modules\Structure\Models\Staff;
use Modules\Structure\Models\UserLegalPerson;
use App\Contracts\BaseCrudServiceContract;

/**
 * Класс сервиса юридических лиц сотрудника.
 *
 * @package Modules\Structure\Services
 */
class UserLegalPersonService implements BaseCrudServiceContract
{
	protected $rules = [
		'user_id'            => 'required|integer',
		'position_id'        => 'required|integer',
		'employment_type_id' => 'required|integer',
		'organization_id'    => 'required|integer',
	];

	/**
	 * Получить список.
	 *
	 * @param RequestOptions $options
	 * @return Collection|UserLegalPerson[]
	 */
	public function getAll(RequestOptions $options)
	{
		return UserLegalPerson::applyOptions($options)->get();
	}

	/**
	 * Получить сущность.
	 *
	 * @param int $id
	 * @param RequestOptions $options
	 * @return UserLegalPerson
	 */
	public function getOne($id, RequestOptions $options = null)
	{
		return UserLegalPerson::applyOptions($options)->findOrFail($id);
	}

	/**
	 * Создать.
	 *
	 * @param RequestOptions $options
	 * @return UserLegalPerson
	 * @throws \App\Exceptions\ValidationException
	 */
	public function create(RequestOptions $options = null)
	{
		UserLegalPerson::validate($options->getData(), $this->rules);

		$userLegalPerson = UserLegalPerson::create($options->getData());
		$this->assignStaffIfNeeded($userLegalPerson);

		return $userLegalPerson;
	}

	/**
	 * Назначить сотрудника в структуру юр. лица, если требуется.
	 * 
	 * @param UserLegalPerson $userLegalPerson
	 */
	public function assignStaffIfNeeded(UserLegalPerson $userLegalPerson)
	{
		$structure = Organization::where('id', $userLegalPerson->organization_id)->first();
		$department = $this->getDepartmentForOrganization($structure);

		if (!Staff::where('department_id', $department->id)
			->where('user_id', $userLegalPerson->user_id)
			->where('position_id', $userLegalPerson->position_id)
			->first()
		) {
			/** @var StaffService $staffService */
			$staffService = app(StaffService::class);
			$staffService->create([
				'data' => [
					'department_id' => $department->id,
					'user_id'       => $userLegalPerson->user_id,
					'position_id'   => $userLegalPerson->position_id,
				]
			]);
		}
	}

	/**
	 * Получить отдел для юридической структуры, куда будут назначены сотрудники.
	 * 
	 * @param Organization $structure
	 * @throws ModelNotFoundException
	 * @return Department|null
	 */
	public function getDepartmentForOrganization(Organization $structure)
	{
		$department = Department::where('organization_id', $structure->id)
			->whereNotNull('parent_id')
			->orderBy('id')
			->first();

		if ($department) {
			return $department;
		}

		throw new ModelNotFoundException('Не найден отдел для юридического лица');
	}

	/**
	 * Удалить сотрудника из структуры юр. лица, если требуется.
	 * 
	 * @param UserLegalPerson $userLegalPerson
	 */
	public function removeStaffIfNeeded(UserLegalPerson $userLegalPerson)
	{
		$structure = Organization::where('id', $userLegalPerson->organization_id)->first();
		$department = $this->getDepartmentForOrganization($structure);

		if ($department) {
			$currentStaff = Staff::where('department_id', $department->id)
				->where('user_id', $userLegalPerson->user_id)
				->where('position_id', $userLegalPerson->position_id)
				->first();

			// если нет других назначений - удалить назначение сотрудника в этом юр. лице
			$otherLegalPersons = UserLegalPerson::where('organization_id', $userLegalPerson->organization_id)
				->where('id', '!=', $userLegalPerson->getKey())
				->get();

			if (!$otherLegalPersons->count()) {
				/** @var StaffService $staffService */
				$staffService = app(StaffService::class);
				$staffService->destroy($currentStaff->getKey());
			}
		}
	}

	/**
	 * Обновить.
	 *
	 * @param int $id
	 * @param RequestOptions $options
	 * @return UserLegalPerson
	 * @throws \App\Exceptions\ValidationException
	 */
	public function update($id, RequestOptions $options)
	{
		UserLegalPerson::validate($options->getData(), $this->rules);

		/** @var UserLegalPerson $userLegalPerson */
		$userLegalPerson = UserLegalPerson::findOrFail($id);
		$userLegalPerson->update($options->getData());
		$this->removeStaffIfNeeded($userLegalPerson);
		$this->assignStaffIfNeeded($userLegalPerson);

		return UserLegalPerson::applyOptions($options)->findOrFail($id);
	}

	/**
	 * Удалить. Так-же сотрудник может удалиться из штата юридических лиц.
	 *
	 * @param int $id
	 * @throws \Throwable
	 * @return UserLegalPerson
	 */
	public function destroy($id)
	{
		\DB::beginTransaction();
		try {
			/** @var UserLegalPerson $userLegalPerson */
			$userLegalPerson = UserLegalPerson::findOrFail($id);
			$userLegalPerson->delete();

			$this->removeStaffIfNeeded($userLegalPerson);
			\DB::commit();
		} catch (\Throwable $e) {
			\DB::rollBack();
			throw $e;
		}

		return $userLegalPerson;
	}
}
