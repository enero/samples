<?php

namespace Modules\Structure\Models;


use App\Models\Tis;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

/**
 * Модель грейда должности.
 *
 * @property int $id
 * @property string $name
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read Collection|Position[] $positions должности
 * @package Modules\Structure\Models
 */
class Grade extends Tis
{
    protected $table = 'structure_grades';

    protected $fillable = ['name'];

    /**
     * Получить список должностей.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function positions()
    {
        return $this->hasMany(Position::class);
    }
}
