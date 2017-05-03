<?php

namespace Modules\Structure\Transformers;

use Modules\Structure\Models\Position;
use League\Fractal\TransformerAbstract;

/**
 * Класс трансформера должности.
 *
 * @package Modules\Structure\Transformers
 */
class PositionTransformer extends TransformerAbstract
{
	/**
	 * @inheritdoc
	 */
	protected $availableIncludes = [
		'grade',
	];

	/**
	 *
	 * @param Position $position
	 * @return array
	 */
	public function transform(Position $position)
	{
		return [
			'id'                   => (int) $position->id,
			'name'                 => $position->name,
			'assigned_users_count' => (int) $position->assigned_users_count,
			'is_legal_entity'      => (int) $position->is_legal_entity,
		];
	}

	/**
	 * @param Position $position
	 * @return \League\Fractal\Resource\Item|null
	 */
	public function includeGrade(Position $position)
	{
		return ($position->relationLoaded('grade') && $position->grade ? $this->item($position->grade, new GradeTransformer()) : null);
	}
}
