<?php

namespace Modules\Structure\Transformers;

use League\Fractal\TransformerAbstract;
use League\Fractal\Resource\Item;
use Modules\Structure\Models\Staff;

/**
 * Класс трансформера должности.
 *
 * @package Modules\Structure\Transformers
 */
class StaffTransformer extends TransformerAbstract
{
	/**
	 * @inheritdoc
	 */
	protected $availableIncludes = [
		'position',
		'user',
		'department',
	];

	/**
	 * Трансформировать данные.
	 *
	 * @param Staff $staff
	 * @return array
	 */
	public function transform(Staff $staff)
	{
		return [
			'id'                   => $staff->id,
			'department_id'        => $staff->department_id,
			'position_id'          => ($staff->position_id ? (int) $staff->position_id : null),
			'user_id'              => ($staff->user_id ? (int) $staff->user_id : null),
			'is_head'              => (bool) $staff->is_head,
			'is_main_job'          => (bool) $staff->is_main_job,
			'employment'           => $staff->employment,
			'work_character'       => $staff->work_character,
			'schedule_days'        => $staff->schedule_days,
			'schedule_time_begin'  => $staff->schedule_time_begin,
			'schedule_time_end'    => $staff->schedule_time_end,
			'is_probation'         => (bool) $staff->is_probation,
			'probation_date_begin' => $staff->probation_date_begin,
			'probation_date_end'   => $staff->probation_date_end,
		];
	}

	/**
	 * Включить должность.
	 *
	 * @param Staff $staff
	 * @return Item|null
	 */
	public function includePosition(Staff $staff)
	{
		return ($staff->position ? $this->item($staff->position, new PositionTransformer()) : null);
	}

	/**
	 * Включить сотрудника.
	 *
	 * @param Staff $staff
	 * @return Item|null
	 */
	public function includeUser(Staff $staff)
	{
		return ($staff->user ? $this->item($staff->user, new UserTransformer()) : null);
	}

	/**
	 * Включить отдел.
	 *
	 * @param Staff $staff
	 * @return Item
	 */
	public function includeDepartment(Staff $staff)
	{
		return $this->item($staff->department, new DepartmentTransformer());
	}
}
