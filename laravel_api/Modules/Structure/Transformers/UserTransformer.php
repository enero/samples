<?php

namespace Modules\Structure\Transformers;

use App\Models\Core\User;
use League\Fractal\TransformerAbstract;
use League\Fractal\Resource\Collection;

/**
 * Класс трансформера сотрудника.
 *
 * @package Modules\Structure\Transformers
 */
class UserTransformer extends TransformerAbstract
{
	/**
	 * @inheritdoc
	 */
	protected $availableIncludes = [
		'staffs',
	];

	/**
	 * Трансформировать данные.
	 *
	 * @param User $user
	 * @return array
	 */
	public function transform(User $user)
	{
		return [
			'id'                => $user->id,
			'is_disabled'       => (bool) $user->is_disabled,
			'name'              => $user->name,
			'full_name'         => $user->full_name,
			'birthday'          => $user->birthday,
			'first_working_day' => $user->first_working_day,
			'is_tech_account'   => (bool) $user->is_tech_account,
			'is_fired'          => (bool) $user->is_fired,
		];
	}

	/**
	 * Включить список персонала.
	 *
	 * @param User $user
	 * @return Collection|null
	 */
	public function includeStaffs(User $user)
	{
		return ($user->staffs->count() ? $this->collection($user->staffs, new StaffTransformer()) : null);
	}

	/**
	 * Включить список юридических лиц.
	 *
	 * @param User $user
	 * @return Collection|null
	 */
	public function includeUserLegalPersons(User $user)
	{
		return ($user->relationLoaded('userLegalPersons') ? $this->collection($user->userLegalPersons, new UserLegalPersonTransformer()) : null);
	}
}
