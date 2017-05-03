<?php

namespace Modules\Structure\Transformers;

use League\Fractal\TransformerAbstract;
use Modules\Structure\Models\Grade;

/**
 * Class GradeTransformer.
 * Транформер грейда.
 * 
 * @package Modules\Structure\Transformers
 */
class GradeTransformer extends TransformerAbstract
{
    /**
     * @inheritdoc
     */
    protected $availableIncludes = [
        'positions',
    ];

    /**
     * Преобразовать грейд.
     *
     * @param Grade $grade
     * @return array
     */
    public function transform(Grade $grade)
    {
        $result = [
            'id'         => (int) $grade->id,
            'name'       => $grade->name,
            'created_at' => $grade->created_at,
            'updated_at' => $grade->updated_at,
        ];

        return $result;
    }

    /**
     * Включить должности.
     *
     * @param Grade $grade
     * @return \League\Fractal\Resource\Collection|null
     */
    public function includePositions(Grade $grade)
    {
        return ($grade->relationLoaded('positions') ? $this->collection($grade->positions, new PositionTransformer()) : null);
    }
}
