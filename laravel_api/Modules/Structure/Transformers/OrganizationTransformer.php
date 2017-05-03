<?php

namespace Modules\Structure\Transformers;

use App\Support\RequestOptions;
use Modules\Structure\Services\DepartmentService;
use Modules\Structure\Models\Organization;
use League\Fractal\TransformerAbstract;
use League\Fractal\Resource\Collection;

/**
 * Класс трансформера организации.
 *
 * @package Modules\Structure\Transformers
 */
class OrganizationTransformer extends TransformerAbstract
{
	/**
	 * @inheritdoc
	 */
	protected $availableIncludes = [
		'departments',
		'departmentsTree',
	];

	/**
	 * Трансформировать данные.
	 *
	 * @param Organization $organization
	 * @return array
	 */
	public function transform(Organization $organization)
	{
		$result = [
			'id'              => $organization->id,
			'short_name'      => $organization->short_name,
			'full_name'       => $organization->full_name,
			'is_legal_entity' => (bool) $organization->is_legal_entity,
		];

		if (in_array('departmentsTree', $this->getCurrentScope()->getManager()->getRequestedIncludes())) {
			$options = new RequestOptions();
			$options->setData(['organization_id' => $organization->id]);
			$departmentsTree = app(DepartmentService::class)->getAll($options);
			$result['departments'] = (new DepartmentTransformer())->transform($departmentsTree);
		}

		return $result;
	}

	/**
	 * Включить список отделов.
	 *
	 * @param Organization $organization
	 * @return Collection
	 */
	public function includeDepartments(Organization $organization)
	{
		$departments = $organization->departments;

		return $this->collection($departments, new DepartmentTransformer());
	}

	/**
	 * Включить дерево отделов.
	 */
	public function includeDepartmentsTree()
	{
		// Метод пока нужен, чтобы свойство departmentsTree не возвращалось.
		// Добавление параметра происходит в transform
	}
}
