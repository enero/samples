<?php

namespace Modules\Structure\Services;

use Illuminate\Database\Eloquent\Collection;
use App\Support\RequestOptions;
use App\Models\Core\User;
use Modules\Structure\Jobs\UpdateStaffRelationsByFormData;
use Modules\Structure\Models\Staff;
use Modules\Structure\Models\Department;
use App\Contracts\BaseCrudServiceContract;
use App\Exceptions\ConstraintException;
use App\Exceptions\ValidationException;

/**
 * Класс сервиса штата.
 *
 * @package Modules\Structure\Services
 */
class StaffService implements BaseCrudServiceContract
{
	/**
	 * Правила валидации
	 *
	 * @var array
	 */
	protected $validateRules = [
		'position_id'    => 'required|integer',
		'user_id'        => 'required|integer',
		'is_main_job'    => 'sometimes|boolean',
		'employment'     => 'sometimes|integer',
		'work_character' => 'sometimes|integer',
		'schedule_days'  => 'sometimes|string',
		'is_probation'   => 'sometimes|boolean',
	];

	/**
	 * Создать позицию штата.
	 *
	 * @param RequestOptions $options
	 * @return Staff
	 * @throws ValidationException
	 * @throws ConstraintException
	 */
	public function create(RequestOptions $options)
	{
		//TODO Проверка прав

		Staff::validate($options->getData(), array_merge($this->validateRules, ['department_id'  => 'required|integer']));

		/** @var Department $department */
		$department = Department::findOrFail($options->getData()['department_id']);
		if ($department->assigned_users_count >= $department->max_staff) {
			throw new ConstraintException('Превышено максимальное количество позиций в штате отдела');
		}

		/** @var Staff $staff */
		$staff = \DB::transaction(function () use ($options) {
			$staff = Staff::create($options->getData());
			dispatch(new UpdateStaffRelationsByFormData($staff, $options->getData()));

			return $staff;
		});

		return Staff::applyOptions($options)->findOrFail($staff->id);
	}

	/**
	 * Получить список позиций штата.
	 *
	 * @param RequestOptions $options
	 * @return Staff[]|Collection
	 * @throws ValidationException
	 */
	public function getAll(RequestOptions $options)
	{
		Staff::validate($options->getData(), ['department_id' => 'required|integer']);

		return Staff::getByDepartment($options);
	}

	/**
	 * Получить позицию в штате.
	 *
	 * @param int $id
	 * @param RequestOptions $options
	 * @return Staff
	 */
	public function getOne($id, RequestOptions $options = null)
	{
		return Staff::applyOptions($options)->findOrFail($id);
	}

	/**
	 * Обновить позицию в штате.
	 *
	 * @param int $id
	 * @param RequestOptions $options
	 * @return Staff
	 * @throws ConstraintException
	 * @throws ValidationException
	 */
	public function update($id, RequestOptions $options)
	{
		Staff::validate($options->getData(), $this->validateRules);

		$staff = Staff::findOrFail($id);

		if (!empty($options->getData()['is_main_job'])) {
			if (!$this->canSetIsMainJob($staff->user, $staff)) {
				throw new ConstraintException('Основная должность у сотрудника уже указана');
			}
		}

		\DB::transaction(function () use ($staff, $options) {
			$staff->update($options->getData());

			dispatch(new UpdateStaffRelationsByFormData($staff, $options->getData()));
		});

		return Staff::applyOptions($options)->findOrFail($id);
	}

	/**
	 * Удалить позицию штата.
	 *
	 * @param int $id
	 * @return Staff
	 */
	public function destroy($id)
	{
		/** @var Staff $staff */
		$staff = Staff::findOrFail($id);
		$staff->delete();

		return $staff;
	}

	/**
	 * Проверить, можно ли указать "основное место работы" для сотрудника.
	 *
	 * @param User $user
	 * @param Staff|null $staff - null если позиция ещё не создана
	 * @return bool
	 */
	public function canSetIsMainJob(User $user, Staff $staff = null)
	{
		if ($staff && !$staff->exists) {
			$staff = null;
		}
		$user->load('staffs');
		$mainJobStaffs = $user->staffs->filter(function (Staff $staff) {
			return $staff->is_main_job;
		});

		if ($mainJobStaffs->count() == 0) {
			return true;
		} else if ($staff) {
			return (bool)$mainJobStaffs->find($staff->getKey(), false);
		} else {
			return false;
		}
	}
}
