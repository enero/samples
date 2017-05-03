<?php

namespace Modules\Structure\Transformers;

use Illuminate\Database\Eloquent\Collection;
use Modules\Structure\Models\Department;
use League\Fractal\TransformerAbstract;

/**
 * Класс трансформера отдела.
 *
 * @package Modules\Structure\Transformers
 */
class DepartmentTransformer extends TransformerAbstract
{
	/**
	 * @inheritdoc
	 */
	protected $availableIncludes = [
		'staff',
	];

	/**
	 * Преобразовать в коллекцию трансформера.
	 *
	 * @param Department $department
	 * @return array
	 */
	public function transform(Department $department)
	{
		return [
			'id'                   => $department->id,
			'organization_id'      => $department->organization_id,
			'name'                 => $department->name,
			'code'                 => $department->code,
			'assigned_users_count' => $department->assigned_users_count,
			'max_staff'            => $department->max_staff,
			'parent_id'            => $department->parent_id,
			'depth'                => $department->depth,
			'children'             => $this->transformChildren($department->children),
		];
	}

	/**
	 * Включить список персонала.
	 *
	 * @param Department $department
	 * @return Collection|null
	 */
	public function includeStaff(Department $department)
	{
		if ($department->relationLoaded('staff') && $department->staff) {
			return $this->collection($department->staff, new StaffTransformer());
		} else {
			return null;
		}
	}

	/**
	 * Трансформировать потомков узла дерева.
	 *
	 * @param Collection $children
	 *
	 * @return array
	 */
	public function transformChildren(Collection $children)
	{
		$result = [];
		foreach ($children as $department) {
			$result[] = $this->transform($department);
		}

		return $result;
	}
}
