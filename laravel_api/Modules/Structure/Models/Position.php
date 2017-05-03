<?php

namespace Modules\Structure\Models;

use App\Models\Tis;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Carbon\Carbon;

/**
 * Класс модели должности.
 *
 * @package Modules\Structure\Models
 *
 * @property int $id
 * @property int $grade_id
 * @property string $name название должности
 * @property bool $is_legal_entity
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Staff $users
 * @property-read int $assigned_users_count
 * @property Builder|QueryBuilder $usersCountByPosition
 * @property-read Grade|null $grade грейд
 * @method static Position getModel()
 * @method static QueryBuilder|Builder headOfHrDepartment()
 */
class Position extends Tis
{
	protected $table = 'structure_positions';

	protected $fillable = ['name', 'is_legal_entity', 'grade_id'];

	/**
	 * Получить данные по штату для должности.
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\HasMany
	 */
	public function staff()
	{
		return $this->hasMany(Staff::class);
	}

	/**
	 * @TODO потом сделать нормально
	 *
	 * Применить условие для выборки руководителя HR отдела.
	 *
	 * @param QueryBuilder|Builder $query
	 * @return QueryBuilder|Builder
	 */
	public function scopeHeadOfHrDepartment($query)
	{
		return $query->where('name', 'Руководитель HR-отдела');
	}

	/**
	 * Вычислить количество назначенных сотрудников с групировкой по должностям.
	 *
	 * @return Builder|QueryBuilder
	 */
	public function usersCount()
	{
		/** @var Builder|QueryBuilder $query */
		$query = $this->hasOne(Staff::class);
		$query->selectRaw('position_id, COUNT(*) AS number');
		$query->whereNotNull('user_id');
		$query->groupBy('position_id');

		return $query;
	}

	/**
	 * Получить количество назначенных сотрудников на должность.
	 *
	 * @return int
	 */
	public function getAssignedUsersCountAttribute()
	{
		if (!array_key_exists('usersCount', $this->relations)) {
			$this->load('usersCount');
		}

		$related = $this->getRelation('usersCount');

		return ($related ? (int) $related->number : 0);
	}

	/**
	 * Получить грейд.
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function grade()
	{
		return $this->belongsTo(Grade::class);
	}
}
