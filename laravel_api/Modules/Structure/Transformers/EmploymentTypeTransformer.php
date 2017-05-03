<?php

namespace Modules\Structure\Transformers;

use Modules\Structure\Models\EmploymentType;
use League\Fractal\TransformerAbstract;

/**
 * Класс трансформера типа занятости.
 *
 * @package Modules\Structure\Transformers
 */
class EmploymentTypeTransformer extends TransformerAbstract
{
    /**
     * Трансформировать данные.
     *
     * @param EmploymentType $type
     * @return array
     */
    public function transform(EmploymentType $type)
    {
        return [
            'id'   => (int) $type->id,
            'name' => $type->name,
        ];
    }
}
