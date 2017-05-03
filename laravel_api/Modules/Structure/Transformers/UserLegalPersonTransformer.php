<?php

namespace Modules\Structure\Transformers;

use Modules\Structure\Models\EmploymentType;
use League\Fractal\TransformerAbstract;
use Modules\Structure\Models\UserLegalPerson;

/**
 * Класс трансформера юридического лица сотрудника.
 *
 * @package Modules\Structure\Transformers
 */
class UserLegalPersonTransformer extends TransformerAbstract
{
	/**
	 * Трансформировать данные.
	 *
	 * @param EmploymentType $type
	 * @return array
	 */
	public function transform(UserLegalPerson $legalPerson)
	{
		return [
			'id'                 => (int) $legalPerson->id,
			'user_id'            => (int) $legalPerson->user_id,
			'position_id'        => (int) $legalPerson->position_id,
			'employment_type_id' => (int) $legalPerson->employment_type_id,
			'organization_id'    => (int) $legalPerson->organization_id,
		];
	}
}
